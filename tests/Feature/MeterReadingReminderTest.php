<?php

namespace Tests\Feature;

use App\Models\Facility;
use App\Models\FacilityMeter;
use App\Models\FacilityMeterWeeklyReading;
use App\Models\Notification;
use App\Models\Setting;
use App\Models\User;
use App\Services\MeterReadingReminderService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MeterReadingReminderTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_dispatches_reminders_for_facilities_with_missing_weekly_readings(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff',
            'status' => 'active',
        ]);

        $facility = Facility::factory()->create([
            'name' => 'San Bartolome Health Center',
            'status' => 'active',
        ]);
        $facility->users()->attach($staff->id);

        $mainMeter = FacilityMeter::create([
            'facility_id' => $facility->id,
            'meter_name' => 'Main Building Meter',
            'meter_type' => 'main',
            'meter_number' => 'MB-1010',
            'status' => 'approved',
            'is_archived' => false,
        ]);

        $service = app(MeterReadingReminderService::class);
        $result = $service->sendReminders(force: true);

        $this->assertSame('success', $result['status']);
        $this->assertGreaterThanOrEqual(1, $result['sent']);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $staff->id,
            'type' => MeterReadingReminderService::TYPE,
            'title' => "⚡ Meter Reading Reminder: {$facility->name}",
        ]);

        $notification = Notification::where('user_id', $staff->id)->first();
        $this->assertNotNull($notification);
        $this->assertStringContainsString('San Bartolome Health Center', $notification->message);
        $this->assertStringContainsString('facilities/' . $facility->id . '/monthly-records', $notification->target_url);
    }

    public function test_it_does_not_send_reminder_if_weekly_reading_is_already_recorded(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff',
            'status' => 'active',
        ]);

        $facility = Facility::factory()->create([
            'name' => 'City Hall Main Wing',
            'status' => 'active',
        ]);
        $facility->users()->attach($staff->id);

        $mainMeter = FacilityMeter::create([
            'facility_id' => $facility->id,
            'meter_name' => 'Main Utility Meter',
            'meter_type' => 'main',
            'meter_number' => 'CH-2020',
            'status' => 'approved',
            'is_archived' => false,
        ]);

        $now = Carbon::now('Asia/Manila');
        $service = app(MeterReadingReminderService::class);
        $weekNumber = $service->calculateWeekNumber((int) $now->day);

        FacilityMeterWeeklyReading::create([
            'facility_id' => $facility->id,
            'meter_id' => $mainMeter->id,
            'year' => (int) $now->year,
            'month' => (int) $now->month,
            'week_number' => $weekNumber,
            'reading_date' => $now->toDateString(),
            'actual_kwh' => 250.00,
            'rate_per_kwh' => 12.00,
            'cost' => 3000.00,
            'encoded_by' => $staff->id,
        ]);

        $result = $service->sendReminders(force: true);

        $this->assertSame('success', $result['status']);
        $this->assertSame(0, $result['sent']);
        $this->assertDatabaseMissing('notifications', [
            'user_id' => $staff->id,
            'type' => MeterReadingReminderService::TYPE,
        ]);
    }

    public function test_it_does_not_send_duplicate_reminders_on_the_same_day(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff',
            'status' => 'active',
        ]);

        $facility = Facility::factory()->create([
            'name' => 'Novaliches District Hall',
            'status' => 'active',
        ]);
        $facility->users()->attach($staff->id);

        FacilityMeter::create([
            'facility_id' => $facility->id,
            'meter_name' => 'Main District Meter',
            'meter_type' => 'main',
            'meter_number' => 'ND-3030',
            'status' => 'approved',
            'is_archived' => false,
        ]);

        $service = app(MeterReadingReminderService::class);

        // First run: sends reminder
        $firstRun = $service->sendReminders(force: true);
        $this->assertSame(1, $firstRun['sent']);

        // Second run on same day: should not duplicate
        $secondRun = $service->sendReminders(force: true);
        $this->assertSame(0, $secondRun['sent']);

        $this->assertSame(1, Notification::where('user_id', $staff->id)->where('type', MeterReadingReminderService::TYPE)->count());
    }

    public function test_it_respects_disabled_setting(): void
    {
        Setting::setValue('enable_reading_reminders', '0');

        $staff = User::factory()->create([
            'role' => 'staff',
            'status' => 'active',
        ]);

        $facility = Facility::factory()->create(['status' => 'active']);
        $facility->users()->attach($staff->id);

        FacilityMeter::create([
            'facility_id' => $facility->id,
            'meter_name' => 'Main Meter',
            'meter_type' => 'main',
            'status' => 'approved',
            'is_archived' => false,
        ]);

        $service = app(MeterReadingReminderService::class);
        $result = $service->sendReminders(force: false);

        $this->assertSame('disabled', $result['status']);
        $this->assertSame(0, $result['sent']);
        $this->assertDatabaseMissing('notifications', ['type' => MeterReadingReminderService::TYPE]);
    }

    public function test_artisan_command_executes_successfully(): void
    {
        $this->artisan('energy:send-reading-reminders --force')
            ->assertExitCode(0);
    }

    public function test_it_sends_email_notification_when_email_is_enabled(): void
    {
        \Illuminate\Support\Facades\Mail::fake();
        Setting::setValue('enable_email_notifications', '1');

        $staff = User::factory()->create([
            'role' => 'staff',
            'status' => 'active',
            'email' => 'encoder@qc.gov.ph',
            'name' => 'QC Staff Encoder',
        ]);

        $facility = Facility::factory()->create([
            'name' => 'Batasan Civic Center',
            'status' => 'active',
        ]);
        $facility->users()->attach($staff->id);

        FacilityMeter::create([
            'facility_id' => $facility->id,
            'meter_name' => 'Main Civic Meter',
            'meter_type' => 'main',
            'status' => 'approved',
            'is_archived' => false,
        ]);

        $service = app(MeterReadingReminderService::class);
        $service->sendReminders(force: true);

        \Illuminate\Support\Facades\Mail::assertSent(\App\Mail\MeterReadingReminderMail::class, function (\App\Mail\MeterReadingReminderMail $mail) use ($staff) {
            return $mail->hasTo($staff->email) && str_contains($mail->subject, 'Batasan Civic Center');
        });
    }
}
