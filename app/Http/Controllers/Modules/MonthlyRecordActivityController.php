<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\EnergyRecord;
use App\Models\Facility;
use App\Support\RoleAccess;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MonthlyRecordActivityController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (! RoleAccess::can($user, 'view_monthly_record_activity')) {
            return redirect()
                ->route('dashboard.index')
                ->with('error', 'You do not have permission to view monthly record activity.');
        }

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'source' => ['nullable', 'in:manual,cprf'],
            'month' => ['nullable', 'date_format:Y-m'],
            'status' => ['nullable', 'in:for_review,approved,returned'],
        ]);

        $search = trim((string) ($validated['search'] ?? ''));
        $source = (string) ($validated['source'] ?? '');
        $month = (string) ($validated['month'] ?? '');
        $status = (string) ($validated['status'] ?? '');

        $records = EnergyRecord::query()
            ->with([
                'facility:id,name,department',
                'recordedBy:id,full_name,name,username,role',
                'meter:id,meter_name,meter_type',
                'reviewer:id,full_name,name,username',
            ])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery
                        ->where('recorded_by_name', 'like', "%{$search}%")
                        ->orWhereHas('recordedBy', function ($userQuery) use ($search) {
                            $userQuery
                                ->where('full_name', 'like', "%{$search}%")
                                ->orWhere('name', 'like', "%{$search}%")
                                ->orWhere('username', 'like', "%{$search}%");
                        })
                        ->orWhereHas('facility', function ($facilityQuery) use ($search) {
                            $facilityQuery
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere('department', 'like', "%{$search}%");
                        });
                });
            })
            ->when($source !== '', fn ($query) => $query->where('input_source', $source))
            ->when($status !== '', fn ($query) => $query->where('review_status', $status))
            ->when($month !== '', function ($query) use ($month) {
                [$year, $monthNumber] = array_map('intval', explode('-', $month));
                $query->where('year', $year)->where('month', $monthNumber);
            })
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $latestRecordId = EnergyRecord::query()
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->value('id');

        $reviewCounts = EnergyRecord::query()
            ->selectRaw("COALESCE(review_status, 'for_review') AS status_key, COUNT(*) AS total")
            ->groupBy('status_key')
            ->pluck('total', 'status_key');

        $currentMonth = now()->startOfMonth();
        $missingMonthlyFacilities = Facility::query()
            ->where('status', 'active')
            ->whereDoesntHave('energyRecords', function ($query) use ($currentMonth) {
                $query
                    ->where('year', $currentMonth->year)
                    ->where('month', $currentMonth->month);
            })
            ->orderBy('name')
            ->get(['id', 'name', 'department']);

        $user->notifications()
            ->where('type', 'monthly_record_submission')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        view()->share('unreadMonthlySubmissionCount', 0);

        return view('modules.monthly-record-activity.index', compact(
            'records',
            'latestRecordId',
            'search',
            'source',
            'month',
            'status',
            'reviewCounts',
            'missingMonthlyFacilities',
            'currentMonth'
        ));
    }

    public function review(Request $request, EnergyRecord $record)
    {
        if (! RoleAccess::can($request->user(), 'review_monthly_records')) {
            return redirect()
                ->route('dashboard.index')
                ->with('error', 'You do not have permission to review monthly records.');
        }

        $validated = $request->validate([
            'review_status' => ['required', 'in:approved,returned'],
            'review_remarks' => ['nullable', 'string', 'max:2000', 'required_if:review_status,returned'],
        ]);

        $record->forceFill([
            'review_status' => $validated['review_status'],
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'review_remarks' => trim((string) ($validated['review_remarks'] ?? '')) ?: null,
        ])->saveQuietly();

        if ($record->recorded_by && (int) $record->recorded_by !== (int) $request->user()->id) {
            $period = Carbon::create((int) $record->year, (int) $record->month, 1)->format('F Y');
            $facility = $record->facility?->name ?? 'Unknown Facility';
            $approved = $validated['review_status'] === 'approved';
            $message = "Your {$period} monthly record for {$facility} was ".($approved ? 'approved.' : 'returned for correction.');
            if (! $approved && filled($validated['review_remarks'] ?? null)) {
                $message .= ' Remarks: '.trim($validated['review_remarks']);
            }

            $record->recordedBy?->notifications()->create([
                'title' => $approved ? 'Monthly Record Approved' : 'Monthly Record Returned',
                'message' => $message,
                'type' => 'monthly_record_review',
                'target_url' => route('facilities.monthly-records', [
                    'facility' => $record->facility_id,
                    'year' => $record->year,
                ]),
            ]);
        }

        return redirect()
            ->back()
            ->with('success', $validated['review_status'] === 'approved'
                ? 'Monthly record approved successfully.'
                : 'Monthly record returned to the encoder.');
    }

}
