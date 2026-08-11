<?php

use App\Models\EnergyRecord;
use App\Models\EnergySavingRecommendation;
use App\Models\Facility;
use App\Models\FacilityMeter;
use App\Models\Notification;
use App\Models\User;
use App\Services\RecommendationNotificationService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

test('monthly records show recommendation status and the matching recommendation action', function () {
    if (! Schema::hasTable('main_meter_readings')) {
        Schema::create('main_meter_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_id');
            $table->date('period_end_date');
            $table->decimal('kwh_used', 14, 2)->default(0);
            $table->string('device_id')->nullable();
            $table->string('input_source')->default('iot');
        });
    }

    $admin = User::factory()->create(['role' => 'super_admin']);
    $staff = User::factory()->create(['role' => 'staff', 'status' => 'active']);
    $facility = Facility::create([
        'name' => 'Health Office',
        'type' => 'Office',
        'floor_area' => 500,
        'status' => 'active',
    ]);
    $meter = FacilityMeter::create([
        'facility_id' => $facility->id,
        'meter_name' => 'Health Office Main Meter',
        'meter_number' => 'MAIN-001',
        'meter_type' => 'main',
        'status' => 'active',
        'baseline_kwh' => 6120,
        'approved_at' => now(),
    ]);
    $staff->facilities()->attach($facility->id);

    $record = EnergyRecord::create([
        'facility_id' => $facility->id,
        'meter_id' => $meter->id,
        'year' => 2026,
        'month' => 7,
        'day' => 21,
        'actual_kwh' => 6460,
        'rate_per_kwh' => 12.35,
    ]);

    EnergySavingRecommendation::create([
        'facility_id' => $facility->id,
        'year' => 2026,
        'month' => 7,
        'generated_message' => 'Review cooling schedules.',
        'engineer_recommendation' => 'Move pre-cooling thirty minutes later.',
        'status' => 'approved',
        'assigned_to' => $staff->id,
        'implementation_status' => 'pending',
        'reviewed_by' => $admin->id,
        'reviewed_at' => now(),
    ]);

    $augustRecord = EnergyRecord::create([
        'facility_id' => $facility->id,
        'meter_id' => $meter->id,
        'year' => 2026,
        'month' => 8,
        'day' => 21,
        'actual_kwh' => 6510,
        'rate_per_kwh' => 12.35,
    ]);

    EnergySavingRecommendation::create([
        'facility_id' => $facility->id,
        'year' => 2026,
        'month' => 8,
        'generated_message' => 'Pending reviewer-only recommendation.',
        'engineer_recommendation' => 'This recommendation is not approved yet.',
        'status' => 'for_review',
        'reviewed_by' => $admin->id,
        'reviewed_at' => now(),
    ]);

    $response = $this->actingAs($admin)->get(route('facilities.monthly-records', [
        'facility' => $facility->id,
        'year' => 2026,
    ]));

    $response->assertOk()
        ->assertSee('Insight')
        ->assertSee('View recommendation')
        ->assertDontSee('Move pre-cooling thirty minutes later.')
        ->assertSee('facility_id='.$facility->id, escape: false)
        ->assertSee('record_id='.$record->id, escape: false)
        ->assertSee('month=2026-07', escape: false);

    $staffMonthlyResponse = $this->actingAs($staff)
        ->get(route('facilities.monthly-records', [
            'facility' => $facility->id,
            'year' => 2026,
        ]));

    $staffMonthlyResponse
        ->assertOk()
        ->assertSee('View recommendation')
        ->assertSee('1 unread recommendation')
        ->assertSee('recommendation_notification_id=', escape: false)
        ->assertDontSee('Move pre-cooling thirty minutes later.')
        ->assertDontSee('System recommendation available')
        ->assertDontSee('This recommendation is not approved yet.')
        ->assertDontSee('Review this month and add a recommendation');

    $julyNotification = Notification::query()
        ->where('user_id', $staff->id)
        ->where('type', RecommendationNotificationService::TYPE)
        ->where('target_url', 'like', '%record_id='.$record->id.'%')
        ->firstOrFail();
    $augustNotification = Notification::query()
        ->where('user_id', $staff->id)
        ->where('type', RecommendationNotificationService::TYPE)
        ->where('target_url', 'like', '%record_id='.$augustRecord->id.'%')
        ->firstOrFail();

    $this->actingAs($staff)
        ->get(route('modules.energy-conservation.feature', [
            'feature' => 'energy-saving-tips',
            'facility_id' => $facility->id,
            'record_id' => $record->id,
            'month' => '2026-07',
            'recommendation_notification_id' => $julyNotification->id,
        ]))
        ->assertOk();

    $this->actingAs($staff)
        ->get(route('modules.energy-conservation.feature', [
            'feature' => 'energy-saving-tips',
            'facility_id' => $facility->id,
            'record_id' => $augustRecord->id,
            'month' => '2026-08',
            'recommendation_notification_id' => $augustNotification->id,
        ]))
        ->assertOk()
        ->assertSee('System-Generated Recommendation')
        ->assertSee('System /')
        ->assertDontSee('Added Recommendations')
        ->assertDontSee('This recommendation is not approved yet.')
        ->assertDontSee('No monthly energy data is available for a system-generated recommendation.');

    expect($julyNotification->fresh()->read_at)->not->toBeNull()
        ->and($augustNotification->fresh()->read_at)->not->toBeNull();

    $this->actingAs($staff)
        ->get(route('facilities.monthly-records', [
            'facility' => $facility->id,
            'year' => 2026,
        ]))
        ->assertOk()
        ->assertDontSee('1 unread recommendation');

    $this->actingAs($admin)
        ->get(route('modules.energy-conservation.feature', [
            'feature' => 'energy-saving-tips',
            'facility_id' => $facility->id,
            'record_id' => $record->id,
            'month' => '2026-07',
        ]))
        ->assertOk()
        ->assertSee('Selected monthly record context')
        ->assertSee('Health Office')
        ->assertSee('July 2026')
        ->assertSee('July 21, 2026')
        ->assertSee('Health Office Main Meter')
        ->assertSee('Back to Monthly Records')
        ->assertSee('Monthly Record Assessment')
        ->assertSee('Actual Usage')
        ->assertSee('Approved Baseline')
        ->assertSee('+340.00 kWh')
        ->assertSee('+5.56%')
        ->assertSee('This assessment is automatically attached to the saved and published recommendation.')
        ->assertSee('Add Recommendation')
        ->assertSee('<label>Recommendation</label>', escape: false)
        ->assertSee('does not create or assign an implementation task')
        ->assertSee('Publish Recommendation')
        ->assertDontSee('Assignment &amp; Handoff', escape: false)
        ->assertSee('Use AI Alerts Suggestion')
        ->assertSee('Open AI Alerts')
        ->assertSee('AI Alerts suggestion is only a draft')
        ->assertSee('AI Alerts suggestion')
        ->assertSee('Reviewer approval')
        ->assertSee('Facility recommendation')
        ->assertSee('System-Generated Recommendation')
        ->assertSee('Added Recommendations')
        ->assertSee('Recommendation Details')
        ->assertSee('Save Changes')
        ->assertSee('Delete Recommendation')
        ->assertDontSee('Progress Status')
        ->assertDontSee('Target Date')
        ->assertDontSee('Expected Savings')
        ->assertDontSee('Actual Savings')
        ->assertDontSee('Implementation Notes')
        ->assertDontSee('<label>Select Facility</label>', escape: false)
        ->assertDontSee('id="recommendation_month"', escape: false)
        ->assertDontSee('<option value="0">All facilities</option>', escape: false);

    $this->actingAs($admin)
        ->get(route('modules.energy-conservation.feature', [
            'feature' => 'energy-saving-tips',
            'facility_id' => $facility->id,
            'month' => '2026-07',
        ]))
        ->assertOk()
        ->assertSee('<label>Select Facility</label>', escape: false)
        ->assertSee('id="recommendation_month"', escape: false);

    $this->actingAs($staff)
        ->get(route('modules.energy-conservation.feature', [
            'feature' => 'energy-saving-tips',
            'facility_id' => $facility->id,
            'record_id' => $record->id,
            'month' => '2026-07',
        ]))
        ->assertOk()
        ->assertSee('Move pre-cooling thirty minutes later.')
        ->assertSee('Published To')
        ->assertSee('Approval Status')
        ->assertDontSee('Save Progress')
        ->assertDontSee('Progress Status')
        ->assertDontSee('Target Date')
        ->assertDontSee('Implementation Notes')
        ->assertDontSee('Update Recommendation')
        ->assertDontSee('Delete Recommendation');

    $assignedRecommendation = EnergySavingRecommendation::query()
        ->where('facility_id', $facility->id)
        ->where('month', 7)
        ->firstOrFail();

    $this->actingAs($staff)
        ->patch(route('modules.energy-conservation.tips.progress', $assignedRecommendation), [
            'implementation_status' => 'in_progress',
            'actual_savings_kwh' => 18.5,
            'implementation_notes' => 'Cooling schedule adjustment has started.',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $julyNotification->refresh();
    expect($julyNotification->read_at)->not->toBeNull()
        ->and($julyNotification->title)->toBe('Energy Recommendation');

    $this->actingAs($staff)
        ->get(route('notifications.index'))
        ->assertOk()
        ->assertSee('Energy Recommendation');

    $this->actingAs($staff)
        ->get(route('facilities.monthly-records', [
            'facility' => $facility->id,
            'year' => 2026,
        ]))
        ->assertOk()
        ->assertDontSee('1 unread recommendation');

    $this->assertDatabaseHas('energy_saving_recommendations', [
        'id' => $assignedRecommendation->id,
        'implementation_status' => 'in_progress',
        'actual_savings_kwh' => 18.5,
        'implementation_notes' => 'Cooling schedule adjustment has started.',
    ]);

    $this->actingAs($admin)
        ->from(route('modules.energy-conservation.feature', ['feature' => 'energy-saving-tips']))
        ->post(route('modules.energy-conservation.tips.review'), [
            'facility_id' => $facility->id,
            'period' => '2026-07',
            'status' => 'approved',
            'engineer_recommendation' => 'A facility recommendation can be published without creating a task.',
            'implementation_status' => 'pending',
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $this->actingAs($admin)
        ->post(route('modules.energy-conservation.tips.review'), [
            'facility_id' => $facility->id,
            'period' => '2026-07',
            'record_id' => $record->id,
            'status' => 'approved',
            'engineer_recommendation' => 'Assign an owner and track the cooling schedule change.',
            'expected_savings_kwh' => 150,
            'target_date' => '2026-08-15',
            'assigned_to' => $staff->id,
            'implementation_status' => 'in_progress',
            'actual_savings_kwh' => 42.5,
            'implementation_notes' => 'Cooling schedule was adjusted for the first floor.',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $julyNotification->refresh();
    expect($julyNotification->read_at)->not->toBeNull()
        ->and($julyNotification->title)->toBe('Energy Recommendation');

    $this->assertDatabaseHas('energy_saving_recommendations', [
        'facility_id' => $facility->id,
        'year' => 2026,
        'month' => 7,
        'assigned_to' => null,
        'implementation_status' => 'pending',
        'actual_savings_kwh' => null,
        'implementation_notes' => null,
    ]);

    $addedRecommendation = EnergySavingRecommendation::query()->latest('id')->firstOrFail();
    $this->actingAs($admin)
        ->put(route('modules.energy-conservation.tips.update', $addedRecommendation), [
            'status' => 'approved',
            'engineer_recommendation' => 'Updated action from the recommendation details modal.',
            'assigned_to' => $staff->id,
            'implementation_status' => 'implemented',
            'actual_savings_kwh' => 55,
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->assertDatabaseHas('energy_saving_recommendations', [
        'id' => $addedRecommendation->id,
        'engineer_recommendation' => 'Updated action from the recommendation details modal.',
        'implementation_status' => 'pending',
    ]);

    $this->actingAs($admin)
        ->get(route('facilities.monthly-records', [
            'facility' => $facility->id,
            'year' => 2026,
        ]))
        ->assertOk()
        ->assertSee('Insight')
        ->assertDontSee('Updated action from the recommendation details modal.');

    $this->actingAs($admin)
        ->delete(route('modules.energy-conservation.tips.destroy', $addedRecommendation))
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->assertDatabaseMissing('energy_saving_recommendations', [
        'id' => $addedRecommendation->id,
    ]);
});

test('a meter-linked cprf monthly record is assigned to cprf integration', function () {
    $admin = User::factory()->create(['role' => 'super_admin']);
    $recorder = User::factory()->create([
        'role' => 'staff',
        'status' => 'active',
        'full_name' => 'CPRF Meter Recorder',
    ]);
    $facility = Facility::create([
        'name' => 'CPRF Recommendation Facility',
        'type' => 'Public Facility',
        'floor_area' => 800,
        'status' => 'active',
        'source' => 'cprf',
        'external_ref' => 501,
    ]);
    $recorder->facilities()->attach($facility->id);
    $meter = FacilityMeter::create([
        'facility_id' => $facility->id,
        'meter_name' => 'CPRF Recommendation Main Meter',
        'meter_number' => 'CPRF-REC-501',
        'meter_type' => 'main',
        'status' => 'active',
        'approved_at' => now(),
    ]);
    $record = EnergyRecord::create([
        'facility_id' => $facility->id,
        'meter_id' => $meter->id,
        'year' => 2026,
        'month' => 7,
        'day' => 22,
        'actual_kwh' => 4800,
        'baseline_kwh' => 4000,
        'rate_per_kwh' => 12.50,
        'input_source' => 'cprf',
        'recorded_by' => $recorder->id,
        'recorded_by_name' => 'CPRF Meter Recorder',
    ]);

    $otherFacility = Facility::create([
        'name' => 'Other Reporting Facility',
        'type' => 'Office',
        'status' => 'active',
    ]);
    $otherMeter = FacilityMeter::create([
        'facility_id' => $otherFacility->id,
        'meter_name' => 'Other Main Meter',
        'meter_number' => 'OTHER-REC-001',
        'meter_type' => 'main',
        'status' => 'active',
        'baseline_kwh' => 500,
        'approved_at' => now(),
    ]);
    EnergyRecord::create([
        'facility_id' => $otherFacility->id,
        'meter_id' => $otherMeter->id,
        'year' => 2026,
        'month' => 7,
        'actual_kwh' => 1000,
        'rate_per_kwh' => 10,
    ]);

    $this->actingAs($admin)
        ->get(route('modules.energy-conservation.feature', [
            'feature' => 'energy-saving-tips',
            'facility_id' => $facility->id,
            'record_id' => $record->id,
            'month' => '2026-07',
        ]))
        ->assertOk()
        ->assertSee('Selected monthly record context')
        ->assertSee('CPRF Recommendation Main Meter')
        ->assertSee('CPRF via UMAN')
        ->assertSee('4,800.00')
        ->assertSee('Monthly Record Assessment')
        ->assertSee('Very high consumption')
        ->assertSee('+800.00 kWh')
        ->assertSee('+20.00%')
        ->assertSee('PHP 10,000.00')
        ->assertDontSee('5,800.00')
        ->assertSee('publishes advice to the CPRF recommendation list')
        ->assertSee('Publish to CPRF')
        ->assertDontSee('<select name="assigned_to">', escape: false)
        ->assertSee('Use AI Alerts Suggestion')
        ->assertSee('Open AI Alerts')
        ->assertSee('CPRF recommendation')
        ->assertSee('System-Generated Recommendation')
        ->assertDontSee('No monthly energy data is available for a system-generated recommendation.');

    $this->actingAs($admin)
        ->post(route('modules.energy-conservation.tips.review'), [
            'facility_id' => $facility->id,
            'record_id' => $record->id,
            'period' => '2026-07',
            'status' => 'approved',
            'engineer_recommendation' => 'Review CPRF facility operating schedules.',
            'assigned_to' => $recorder->id,
            'implementation_status' => 'implemented',
            'actual_savings_kwh' => 999,
            'implementation_notes' => 'This must not be accepted from the Energy create form.',
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('energy_saving_recommendations', [
        'facility_id' => $facility->id,
        'year' => 2026,
        'month' => 7,
        'engineer_recommendation' => 'Review CPRF facility operating schedules.',
        'status' => 'approved',
        'assigned_to' => null,
        'implementation_status' => 'pending',
        'actual_savings_kwh' => null,
        'implementation_notes' => null,
    ]);

    $publishedRecommendation = EnergySavingRecommendation::query()->latest('id')->firstOrFail();
    expect($publishedRecommendation->generated_message)
        ->toContain('Monthly record assessment for CPRF Recommendation Facility, July 2026')
        ->toContain('Actual usage 4,800.00 kWh')
        ->toContain('Status: Very high consumption')
        ->toContain('Approved baseline 4,000.00 kWh')
        ->toContain('800.00 kWh (20.00%) above baseline')
        ->toContain('Estimated avoidable cost: PHP 10,000.00');
});

test('recommendation summary requires a baseline before showing avoidable cost', function () {
    $admin = User::factory()->create(['role' => 'super_admin']);
    $staff = User::factory()->create(['role' => 'staff', 'status' => 'active']);
    $facility = Facility::create([
        'name' => 'No Baseline Facility',
        'type' => 'Office',
        'status' => 'active',
    ]);
    $meter = FacilityMeter::create([
        'facility_id' => $facility->id,
        'meter_name' => 'No Baseline Main Meter',
        'meter_number' => 'NO-BASELINE-001',
        'meter_type' => 'main',
        'status' => 'active',
        'approved_at' => now(),
    ]);
    $record = EnergyRecord::create([
        'facility_id' => $facility->id,
        'meter_id' => $meter->id,
        'year' => 2026,
        'month' => 8,
        'actual_kwh' => 4000,
        'rate_per_kwh' => 14.83,
    ]);
    $staff->facilities()->attach($facility->id);

    $this->actingAs($admin)
        ->get(route('modules.energy-conservation.feature', [
            'feature' => 'energy-saving-tips',
            'facility_id' => $facility->id,
            'record_id' => $record->id,
            'month' => '2026-08',
        ]))
        ->assertOk()
        ->assertSee('Baseline required')
        ->assertSee('Monthly Record Assessment')
        ->assertSee('No baseline yet')
        ->assertSee('Not available')
        ->assertSee('Monthly Energy Cost')
        ->assertSee('PHP 59,320.00')
        ->assertSee('does not create or assign an implementation task')
        ->assertSee('baseline is still being established from 3–6 approved monthly readings')
        ->assertSee('Set an approved baseline before estimating excess cost.');

    $this->actingAs($admin)
        ->post(route('modules.energy-conservation.tips.review'), [
            'facility_id' => $facility->id,
            'record_id' => $record->id,
            'period' => '2026-08',
            'status' => 'approved',
            'engineer_recommendation' => 'Begin an operational shutdown checklist while the baseline is being established.',
            'expected_savings_kwh' => 999,
            'assigned_to' => $staff->id,
            'implementation_status' => 'implemented',
            'actual_savings_kwh' => 888,
            'implementation_notes' => 'Must not be accepted during recommendation creation.',
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('energy_saving_recommendations', [
        'facility_id' => $facility->id,
        'year' => 2026,
        'month' => 8,
        'expected_savings_kwh' => null,
        'implementation_status' => 'pending',
        'actual_savings_kwh' => null,
        'implementation_notes' => null,
    ]);

    $meter->update(['baseline_kwh' => 2688.36]);

    $this->actingAs($admin)
        ->get(route('modules.energy-conservation.feature', [
            'feature' => 'energy-saving-tips',
            'facility_id' => $facility->id,
            'record_id' => $record->id,
            'month' => '2026-08',
        ]))
        ->assertOk()
        ->assertDontSee('Baseline required')
        ->assertSee('PHP 19,451.62');
});
