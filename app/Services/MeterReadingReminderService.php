<?php

namespace App\Services;

use App\Mail\MeterReadingReminderMail;
use App\Models\Facility;
use App\Models\FacilityMeter;
use App\Models\FacilityMeterWeeklyReading;
use App\Models\EnergyRecord;
use App\Models\Notification;
use App\Models\User;
use App\Support\RoleAccess;
use App\Support\SystemSettings;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

class MeterReadingReminderService
{
    public const TYPE = 'reading_reminder';

    /**
     * Send meter reading reminders for facilities with missing logs.
     *
     * @param bool $force If true, bypasses the day schedule check.
     * @return array Summary of processed facilities and sent notifications.
     */
    public function sendReminders(bool $force = false): array
    {
        if (! Schema::hasTable('users') || ! Schema::hasTable('notifications')) {
            return ['status' => 'skipped', 'reason' => 'Required database tables not available.', 'sent' => 0];
        }

        $isEnabled = SystemSettings::enabled('enable_reading_reminders', true);
        if (! $isEnabled && ! $force) {
            return ['status' => 'disabled', 'reason' => 'Reading reminders are disabled in settings.', 'sent' => 0];
        }

        $now = Carbon::now(SystemSettings::string('timezone', 'Asia/Manila'));
        $dayOfWeek = strtolower($now->format('l'));
        $dayOfMonth = (int) $now->day;
        $daysInMonth = (int) $now->daysInMonth;
        $year = (int) $now->year;
        $month = (int) $now->month;
        $weekNumber = $this->calculateWeekNumber($dayOfMonth);

        $scheduledDay = strtolower(SystemSettings::string('reading_reminder_day', 'friday'));
        $isScheduledDay = $this->isScheduledDay($scheduledDay, $dayOfWeek, $dayOfMonth, $daysInMonth);

        if (! $isScheduledDay && ! $force) {
            return [
                'status' => 'skipped',
                'reason' => "Today ({$dayOfWeek}) is not the configured scheduled reading day ({$scheduledDay}).",
                'sent' => 0,
            ];
        }

        $frequency = SystemSettings::string('reading_reminder_frequency', 'weekly');
        $checkWeekly = in_array($frequency, ['weekly', 'both'], true);
        $checkMonthly = in_array($frequency, ['monthly', 'both'], true);

        $facilities = Facility::query()
            ->where('status', 'active')
            ->with(['meters' => function ($query) {
                $query->where('meter_type', 'main');
                if (Schema::hasColumn('facility_meters', 'is_archived')) {
                    $query->where('is_archived', false);
                }
            }, 'users' => function ($query) {
                $query->where('status', 'active');
            }])
            ->get();

        $sentCount = 0;
        $remindedFacilities = [];

        foreach ($facilities as $facility) {
            $mainMeters = $facility->meters;
            if ($mainMeters->isEmpty()) {
                continue;
            }

            $hasMissingWeekly = false;
            $hasMissingMonthly = false;

            if ($checkWeekly) {
                foreach ($mainMeters as $meter) {
                    $hasReading = FacilityMeterWeeklyReading::query()
                        ->where('facility_id', $facility->id)
                        ->where('meter_id', $meter->id)
                        ->where('year', $year)
                        ->where('month', $month)
                        ->where('week_number', $weekNumber)
                        ->exists();

                    if (! $hasReading) {
                        $hasMissingWeekly = true;
                        break;
                    }
                }
            }

            if ($checkMonthly && ($dayOfMonth >= 22 || $force)) {
                $hasMonthlyRecord = EnergyRecord::query()
                    ->where('facility_id', $facility->id)
                    ->where('year', $year)
                    ->where('month', $month)
                    ->whereNull('deleted_at')
                    ->exists();

                if (! $hasMonthlyRecord) {
                    $hasMissingMonthly = true;
                }
            }

            if (! $hasMissingWeekly && ! $hasMissingMonthly) {
                continue;
            }

            $recipients = $this->resolveRecipientsForFacility($facility);
            if ($recipients->isEmpty()) {
                continue;
            }

            $periodLabel = date('F Y', mktime(0, 0, 0, $month, 1, $year));
            $title = "⚡ Meter Reading Reminder: {$facility->name}";
            
            if ($hasMissingWeekly) {
                $message = "It is time to record the Week {$weekNumber} meter reading for {$facility->name} ({$periodLabel}). Please log the latest meter dials.";
                $targetUrl = route('facilities.monthly-records', [
                    'facility' => $facility->id,
                    'summary_mode' => 'week',
                    'year' => $year,
                ]);
            } else {
                $message = "It is time to encode the monthly energy record for {$facility->name} ({$periodLabel}). Please log the monthly reading.";
                $targetUrl = route('facilities.monthly-records', [
                    'facility' => $facility->id,
                    'year' => $year,
                ]);
            }

            foreach ($recipients as $recipient) {
                $alreadySentToday = $recipient->notifications()
                    ->where('type', self::TYPE)
                    ->where('title', $title)
                    ->where('created_at', '>=', $now->copy()->startOfDay())
                    ->exists();

                if ($alreadySentToday) {
                    continue;
                }

                $recipient->notifications()->create([
                    'type' => self::TYPE,
                    'title' => $title,
                    'message' => $message,
                    'target_url' => $targetUrl,
                    'read_at' => null,
                ]);

                if (SystemSettings::emailNotificationsEnabled() && filter_var($recipient->email, FILTER_VALIDATE_EMAIL)) {
                    try {
                        Mail::to($recipient->email)->send(new MeterReadingReminderMail(
                            recipientName: (string) ($recipient->name ?: $recipient->email),
                            facilityName: (string) $facility->name,
                            periodLabel: $periodLabel,
                            weekNumber: $weekNumber,
                            messageText: $message,
                            actionUrl: $targetUrl,
                            isWeekly: $hasMissingWeekly,
                        ));
                    } catch (\Throwable) {
                        // In-app bell notification remains delivered even if mail transport encounters an issue
                    }
                }

                $sentCount++;
            }

            $remindedFacilities[] = $facility->name;
        }

        return [
            'status' => 'success',
            'sent' => $sentCount,
            'facilities_reminded' => array_unique($remindedFacilities),
            'week_number' => $weekNumber,
            'year' => $year,
            'month' => $month,
        ];
    }

    /**
     * Calculate week number within the month (1 to 4).
     */
    public function calculateWeekNumber(int $dayOfMonth): int
    {
        if ($dayOfMonth <= 7) {
            return 1;
        }
        if ($dayOfMonth <= 14) {
            return 2;
        }
        if ($dayOfMonth <= 21) {
            return 3;
        }
        return 4;
    }

    /**
     * Check whether current day matches scheduled trigger day.
     */
    private function isScheduledDay(string $scheduledDay, string $dayOfWeek, int $dayOfMonth, int $daysInMonth): bool
    {
        if ($scheduledDay === 'end_of_period') {
            return in_array($dayOfMonth, [7, 14, 21, $daysInMonth], true);
        }

        return $scheduledDay === $dayOfWeek;
    }

    /**
     * Resolve users who should receive reminders for a given facility.
     *
     * @return Collection<int, User>
     */
    private function resolveRecipientsForFacility(Facility $facility): Collection
    {
        // 1. Staff users assigned specifically to this facility
        $assignedStaff = $facility->users
            ->filter(fn (User $user) => RoleAccess::is($user, 'staff') && $user->status === 'active');

        if ($assignedStaff->isNotEmpty()) {
            return $assignedStaff->values();
        }

        // 2. If no direct staff assigned, notify active Energy Officers and Admins
        return User::query()
            ->where('status', 'active')
            ->get()
            ->filter(function (User $user) {
                $role = RoleAccess::normalize($user);
                return in_array($role, ['energy_officer', 'admin', 'super_admin'], true);
            })
            ->values();
    }
}
