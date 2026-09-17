<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\EnergyRecord;
use App\Models\Facility;
use App\Models\FacilityUtilityBudget;
use App\Support\BaselineResolver;
use App\Support\RoleAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Response;

class CashFlowController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (! RoleAccess::can($user, 'view_cashflow')) {
            abort(403, 'Unauthorized access to Utility Cash Flow & Budget Tracking.');
        }

        $canManageBudget = RoleAccess::can($user, 'manage_cashflow');

        $facilities = strtolower((string) ($user->role ?? '')) === 'staff'
            ? $user->facilities()->orderBy('name')->get()
            : Facility::query()->orderBy('name')->get();

        $localFacilities = $facilities->filter(fn ($f) => ! $f->isCprfManaged())->values();
        $cprfFacilities = $facilities->filter(fn ($f) => $f->isCprfManaged())->values();

        $selectedFacilityId = $request->input('facility_id', 'local');
        if ($selectedFacilityId === 'all') {
            $selectedFacilityId = 'local';
        }

        $fiscalYear = (int) $request->input('year', date('Y'));
        if ($fiscalYear < 2020 || $fiscalYear > 2035) {
            $fiscalYear = (int) date('Y');
        }

        $selectedFacility = null;
        if ($selectedFacilityId === 'local') {
            $scopeType = 'local';
            $scopedFacilities = $localFacilities;
            $scopeTitle = 'All Local Facilities (Aggregated)';
        } elseif ($selectedFacilityId === 'cprf') {
            $scopeType = 'cprf';
            $scopedFacilities = $cprfFacilities;
            $scopeTitle = 'All CPRF Integrated Facilities (Aggregated)';
        } else {
            $selectedFacility = $facilities->firstWhere('id', (int) $selectedFacilityId);
            if ($selectedFacility) {
                $scopeType = 'single';
                $scopedFacilities = collect([$selectedFacility]);
                $scopeTitle = $selectedFacility->name;
            } else {
                $selectedFacilityId = 'local';
                $scopeType = 'local';
                $scopedFacilities = $localFacilities;
                $scopeTitle = 'All Local Facilities (Aggregated)';
            }
        }

        // Available fiscal years from energy records and current year
        $recordedYears = EnergyRecord::query()
            ->select('year')
            ->distinct()
            ->pluck('year')
            ->toArray();
        $availableYears = array_unique(array_merge([date('Y'), date('Y') - 1, date('Y') + 1], $recordedYears));
        rsort($availableYears);

        // Fetch Budget and Records for the selected scope
        $data = $this->calculateCashFlowData($scopeType, $selectedFacility, $scopedFacilities, $fiscalYear);

        return view('modules.cashflow.index', array_merge([
            'facilities' => $facilities,
            'localFacilities' => $localFacilities,
            'cprfFacilities' => $cprfFacilities,
            'selectedFacilityId' => $selectedFacilityId,
            'selectedFacility' => $selectedFacility,
            'scopeType' => $scopeType,
            'scopeTitle' => $scopeTitle,
            'fiscalYear' => $fiscalYear,
            'availableYears' => $availableYears,
            'canManageBudget' => $canManageBudget,
        ], $data));
    }

    public function updateBudget(Request $request)
    {
        $user = $request->user();
        if (! RoleAccess::can($user, 'manage_cashflow')) {
            abort(403, 'You do not have permission to manage utility budgets.');
        }

        $validated = $request->validate([
            'facility_id' => ['required', 'exists:facilities,id'],
            'fiscal_year' => ['required', 'integer', 'min:2020', 'max:2035'],
            'annual_budget_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $amount = (float) ($validated['annual_budget_amount'] ?? 0);

        if ($amount > 0) {
            FacilityUtilityBudget::updateOrCreate(
                [
                    'facility_id' => (int) $validated['facility_id'],
                    'fiscal_year' => (int) $validated['fiscal_year'],
                ],
                [
                    'annual_budget_amount' => round($amount, 2),
                    'notes' => $validated['notes'] ?? null,
                    'created_by' => $user->id,
                ]
            );
            $msg = 'Annual utility budget successfully saved.';
        } else {
            // Remove budget if set to 0 or cleared
            FacilityUtilityBudget::where('facility_id', (int) $validated['facility_id'])
                ->where('fiscal_year', (int) $validated['fiscal_year'])
                ->delete();
            $msg = 'Utility budget cleared. Facility is now in pure Cash Flow mode.';
        }

        return redirect()->route('modules.cashflow.index', [
            'facility_id' => $validated['facility_id'],
            'year' => $validated['fiscal_year'],
        ])->with('success', $msg);
    }

    public function export(Request $request)
    {
        $user = $request->user();
        if (! RoleAccess::can($user, 'export_reports')) {
            abort(403, 'Unauthorized export access.');
        }

        $facilities = strtolower((string) ($user->role ?? '')) === 'staff'
            ? $user->facilities()->orderBy('name')->get()
            : Facility::query()->orderBy('name')->get();

        $localFacilities = $facilities->filter(fn ($f) => ! $f->isCprfManaged())->values();
        $cprfFacilities = $facilities->filter(fn ($f) => $f->isCprfManaged())->values();

        $selectedFacilityId = $request->input('facility_id', 'local');
        if ($selectedFacilityId === 'all') {
            $selectedFacilityId = 'local';
        }

        $fiscalYear = (int) $request->input('year', date('Y'));

        $selectedFacility = null;
        if ($selectedFacilityId === 'local') {
            $scopeType = 'local';
            $scopedFacilities = $localFacilities;
            $scopeTitle = 'All Local Facilities (Aggregated)';
        } elseif ($selectedFacilityId === 'cprf') {
            $scopeType = 'cprf';
            $scopedFacilities = $cprfFacilities;
            $scopeTitle = 'All CPRF Integrated Facilities (Aggregated)';
        } else {
            $selectedFacility = $facilities->firstWhere('id', (int) $selectedFacilityId);
            if ($selectedFacility) {
                $scopeType = 'single';
                $scopedFacilities = collect([$selectedFacility]);
                $scopeTitle = $selectedFacility->name;
            } else {
                $selectedFacilityId = 'local';
                $scopeType = 'local';
                $scopedFacilities = $localFacilities;
                $scopeTitle = 'All Local Facilities (Aggregated)';
            }
        }

        $data = $this->calculateCashFlowData($scopeType, $selectedFacility, $scopedFacilities, $fiscalYear);
        $filename = 'utility_cashflow_' . $scopeType . '_' . ($selectedFacility ? 'fac_' . $selectedFacility->id : '') . '_' . $fiscalYear . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($data, $scopeTitle, $scopeType, $fiscalYear) {
            $file = fopen('php://output', 'w');
            // UTF-8 BOM for Excel
            fputs($file, "\xEF\xBB\xBF");

            $hasEnactedBudget = $data['hasEnactedBudget'];

            // Meta summary
            if ($hasEnactedBudget) {
                fputcsv($file, ['LGU UTILITY CASH FLOW & BUDGET STATEMENT']);
                fputcsv($file, ['Scope Title:', $scopeTitle]);
                fputcsv($file, ['Scope Type:', strtoupper($scopeType)]);
                fputcsv($file, ['Fiscal Year:', $fiscalYear]);
                fputcsv($file, ['Total Annual Budget (PHP):', number_format($data['annualBudget'], 2)]);
                fputcsv($file, ['YTD Actual Outflow (PHP):', number_format($data['ytdActualOutflow'], 2)]);
                fputcsv($file, ['Remaining Balance (PHP):', number_format($data['remainingBudget'] ?? 0, 2)]);
                fputcsv($file, ['Budget Burn Rate (%):', ($data['burnRatePct'] ?? 0) . '%']);
                fputcsv($file, ['Total Cost Savings (PHP):', number_format($data['costSavingsYtd'], 2)]);
                fputcsv($file, []);

                // Table headers with Budget
                fputcsv($file, [
                    'Month',
                    'Fiscal Year',
                    'Allocated Budget (PHP)',
                    'Actual Bill Outflow (PHP)',
                    'Consumption (kWh)',
                    'Effective Rate (PHP/kWh)',
                    'Variance (PHP)',
                    'Retained Savings (PHP)',
                    'Status',
                ]);

                foreach ($data['monthlyBreakdown'] as $row) {
                    fputcsv($file, [
                        $row['month_name'],
                        $fiscalYear,
                        round($row['allocated_budget'], 2),
                        round($row['actual_cost'], 2),
                        round($row['actual_kwh'], 2),
                        round($row['avg_rate'], 2),
                        round($row['variance'] ?? 0, 2),
                        round($row['savings'], 2),
                        $row['status'],
                    ]);
                }
            } else {
                fputcsv($file, ['LGU UTILITY CASH FLOW STATEMENT']);
                fputcsv($file, ['Scope Title:', $scopeTitle]);
                fputcsv($file, ['Scope Type:', strtoupper($scopeType)]);
                fputcsv($file, ['Fiscal Year:', $fiscalYear]);
                fputcsv($file, ['Mode:', 'Actual Cash Outflow Tracking (No Enacted Budget)']);
                fputcsv($file, ['YTD Actual Outflow (PHP):', number_format($data['ytdActualOutflow'], 2)]);
                fputcsv($file, ['Billed Months Count:', $data['approvedBillsCount']]);
                fputcsv($file, ['Average Monthly Outflow (PHP):', number_format($data['avgMonthlyOutflow'], 2)]);
                fputcsv($file, ['Projected Year-End Outflow (PHP):', number_format($data['projectedYearEndOutflow'], 2)]);
                fputcsv($file, ['Weighted Average Unit Rate (PHP/kWh):', number_format($data['avgRatePerKwh'], 2)]);
                fputcsv($file, ['Total Cost Savings (PHP):', number_format($data['costSavingsYtd'], 2)]);
                fputcsv($file, []);

                // Table headers for Pure Cash Flow
                fputcsv($file, [
                    'Month',
                    'Fiscal Year',
                    'Actual Bill Outflow (PHP)',
                    'Consumption (kWh)',
                    'Effective Rate (PHP/kWh)',
                    'MoM Change (PHP)',
                    'Retained Savings (PHP)',
                    'Status',
                ]);

                foreach ($data['monthlyBreakdown'] as $row) {
                    fputcsv($file, [
                        $row['month_name'],
                        $fiscalYear,
                        round($row['actual_cost'], 2),
                        round($row['actual_kwh'], 2),
                        round($row['avg_rate'], 2),
                        $row['mom_change_amount'] !== null ? round($row['mom_change_amount'], 2) : 'N/A',
                        round($row['savings'], 2),
                        $row['status'],
                    ]);
                }
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    private function calculateCashFlowData(string $scopeType, ?Facility $selectedFacility, Collection $scopedFacilities, int $fiscalYear): array
    {
        $facilityIds = $scopedFacilities->pluck('id');

        // 1. Fetch Enacted Budgets
        $budgets = FacilityUtilityBudget::query()
            ->whereIn('facility_id', $facilityIds)
            ->where('fiscal_year', $fiscalYear)
            ->get();

        // 2. Fetch Approved Energy Records for this year
        $records = EnergyRecord::query()
            ->with(['facility.energyProfiles', 'meter'])
            ->whereIn('facility_id', $facilityIds)
            ->where('year', $fiscalYear)
            ->where('review_status', 'approved')
            ->whereNotNull('actual_kwh')
            ->where('actual_kwh', '>', 0)
            ->orderBy('month')
            ->get();

        // Fallback rate per kWh
        $latestRecord = EnergyRecord::query()
            ->whereIn('facility_id', $facilityIds)
            ->whereNotNull('rate_per_kwh')
            ->where('rate_per_kwh', '>', 0)
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->first();
        $fallbackRate = $latestRecord ? (float) $latestRecord->rate_per_kwh : 12.00;

        $hasEnactedBudget = false;
        $annualBudget = 0.0;
        $budgetNotes = null;

        if ($scopeType === 'single' && $selectedFacility) {
            $budgetRecord = $budgets->firstWhere('facility_id', $selectedFacility->id);
            if ($budgetRecord && (float) $budgetRecord->annual_budget_amount > 0) {
                $annualBudget = (float) $budgetRecord->annual_budget_amount;
                $hasEnactedBudget = true;
                $budgetNotes = $budgetRecord->notes ?: 'Custom enacted budget for ' . $selectedFacility->name . '.';
            }
        } else {
            // Aggregated scope (local or cprf)
            $enactedCount = 0;
            foreach ($scopedFacilities as $fac) {
                $bRec = $budgets->firstWhere('facility_id', $fac->id);
                if ($bRec && (float) $bRec->annual_budget_amount > 0) {
                    $annualBudget += (float) $bRec->annual_budget_amount;
                    $enactedCount++;
                }
            }
            if ($enactedCount > 0) {
                $hasEnactedBudget = true;
                $budgetNotes = 'Aggregated enacted utility budget for ' . $enactedCount . ' facility/facilities.';
            }
        }

        $monthlyAllocatedBudget = $hasEnactedBudget ? round($annualBudget / 12, 2) : 0.0;
        $recordsByMonth = $records->groupBy('month');

        $monthlyBreakdown = [];
        $ytdActualOutflow = 0.0;
        $ytdActualKwh = 0.0;
        $costSavingsYtd = 0.0;
        $approvedBillsCount = 0;

        $chartLabels = [];
        $chartBudgetData = [];
        $chartOutflowData = [];
        $chartSavingsData = [];

        $prevActualCost = null;

        for ($m = 1; $m <= 12; $m++) {
            $monthRecords = $recordsByMonth->get($m, collect());
            $monthName = date('M', mktime(0, 0, 0, $m, 1));
            $chartLabels[] = $monthName;

            $hasRecord = $monthRecords->isNotEmpty();
            $actualKwh = (float) $monthRecords->sum('actual_kwh');
            
            $actualCost = (float) $monthRecords->sum(function ($r) use ($fallbackRate) {
                if (is_numeric($r->energy_cost) && (float) $r->energy_cost > 0) {
                    return (float) $r->energy_cost;
                }
                $rate = (is_numeric($r->rate_per_kwh) && (float) $r->rate_per_kwh > 0) ? (float) $r->rate_per_kwh : $fallbackRate;
                return (float) $r->actual_kwh * $rate;
            });

            $rate = $actualKwh > 0 ? round($actualCost / $actualKwh, 2) : $fallbackRate;

            $monthSavings = 0.0;
            if ($hasRecord) {
                $monthBaselineKwh = (float) $monthRecords->sum(function ($r) {
                    $base = BaselineResolver::forRecord($r, $r->facility);
                    return is_numeric($base) ? (float) $base : 0.0;
                });
                if ($monthBaselineKwh > $actualKwh) {
                    $monthSavings = round(($monthBaselineKwh - $actualKwh) * $rate, 2);
                }
            }

            // Month-over-Month change
            $momChangeAmount = null;
            $momChangePct = null;
            if ($hasRecord) {
                if ($prevActualCost !== null && $prevActualCost > 0) {
                    $momChangeAmount = round($actualCost - $prevActualCost, 2);
                    $momChangePct = round((($actualCost - $prevActualCost) / $prevActualCost) * 100, 1);
                }
                $prevActualCost = $actualCost;
            }

            if ($hasEnactedBudget) {
                $variance = round($monthlyAllocatedBudget - $actualCost, 2);
                if ($hasRecord) {
                    if ($actualCost <= $monthlyAllocatedBudget) {
                        $status = 'Within Budget';
                        $statusBadgeClass = 'status-within';
                    } elseif ($actualCost <= $monthlyAllocatedBudget * 1.1) {
                        $status = 'Near Limit';
                        $statusBadgeClass = 'status-warning';
                    } else {
                        $status = 'Over Budget';
                        $statusBadgeClass = 'status-danger';
                    }
                } else {
                    $status = 'Upcoming';
                    $statusBadgeClass = 'status-upcoming';
                }
            } else {
                $variance = null;
                if ($hasRecord) {
                    $status = 'Paid & Approved';
                    $statusBadgeClass = 'status-paid';
                } else {
                    $status = 'Upcoming';
                    $statusBadgeClass = 'status-upcoming';
                }
            }

            if ($hasRecord) {
                $approvedBillsCount++;
                $ytdActualOutflow += $actualCost;
                $ytdActualKwh += $actualKwh;
                $costSavingsYtd += $monthSavings;
            }

            $monthlyBreakdown[] = [
                'month' => $m,
                'month_name' => $monthName,
                'has_record' => $hasRecord,
                'allocated_budget' => $monthlyAllocatedBudget,
                'actual_cost' => $actualCost,
                'actual_kwh' => $actualKwh,
                'avg_rate' => $rate,
                'variance' => $variance,
                'mom_change_amount' => $momChangeAmount,
                'mom_change_pct' => $momChangePct,
                'savings' => $monthSavings,
                'status' => $status,
                'status_badge' => $statusBadgeClass,
                'first_record_id' => $monthRecords->first()?->id,
            ];

            if ($hasEnactedBudget) {
                $chartBudgetData[] = $monthlyAllocatedBudget;
            } else {
                $chartBudgetData[] = null;
            }
            $chartOutflowData[] = $hasRecord ? round($actualCost, 2) : null;
            $chartSavingsData[] = round($monthSavings, 2);
        }

        $avgMonthlyOutflow = $approvedBillsCount > 0 ? round($ytdActualOutflow / $approvedBillsCount, 2) : 0.0;
        $avgRatePerKwh = $ytdActualKwh > 0 ? round($ytdActualOutflow / $ytdActualKwh, 2) : $fallbackRate;
        $projectedYearEndOutflow = round($avgMonthlyOutflow * 12, 2);

        if ($hasEnactedBudget) {
            $remainingBudget = round($annualBudget - $ytdActualOutflow, 2);
            $burnRatePct = $annualBudget > 0 ? round(($ytdActualOutflow / $annualBudget) * 100, 1) : 0;
            $projectedDeficitOrSurplus = round($annualBudget - $projectedYearEndOutflow, 2);
        } else {
            $remainingBudget = null;
            $burnRatePct = null;
            $projectedDeficitOrSurplus = null;
        }

        return [
            'hasEnactedBudget' => $hasEnactedBudget,
            'annualBudget' => $annualBudget,
            'isCustomBudget' => $hasEnactedBudget,
            'budgetNotes' => $budgetNotes,
            'ytdActualOutflow' => $ytdActualOutflow,
            'ytdActualKwh' => $ytdActualKwh,
            'approvedBillsCount' => $approvedBillsCount,
            'avgRatePerKwh' => $avgRatePerKwh,
            'remainingBudget' => $remainingBudget,
            'burnRatePct' => $burnRatePct,
            'costSavingsYtd' => $costSavingsYtd,
            'avgMonthlyOutflow' => $avgMonthlyOutflow,
            'projectedYearEndOutflow' => $projectedYearEndOutflow,
            'projectedDeficitOrSurplus' => $projectedDeficitOrSurplus,
            'monthlyBreakdown' => $monthlyBreakdown,
            'chartLabels' => $chartLabels,
            'chartBudgetData' => $chartBudgetData,
            'chartOutflowData' => $chartOutflowData,
            'chartSavingsData' => $chartSavingsData,
        ];
    }
}
