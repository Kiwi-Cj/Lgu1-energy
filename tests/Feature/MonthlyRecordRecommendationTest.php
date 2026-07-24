<?php

use App\Models\EnergyRecord;
use App\Models\EnergySavingRecommendation;
use App\Models\Facility;
use App\Models\FacilityMeter;
use App\Models\User;
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
        'reviewed_by' => $admin->id,
        'reviewed_at' => now(),
    ]);

    $response = $this->actingAs($admin)->get(route('facilities.monthly-records', [
        'facility' => $facility->id,
        'year' => 2026,
    ]));

    $response->assertOk()
        ->assertSee('Recommendation')
        ->assertSee('Approved')
        ->assertSee('Move pre-cooling thirty minutes later.')
        ->assertSee('View Recommendation')
        ->assertSee('facility_id='.$facility->id, escape: false)
        ->assertSee('record_id='.$record->id, escape: false)
        ->assertSee('month=2026-07', escape: false);

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
        ->assertSee('Add Recommendation')
        ->assertSee('Use as Action Recommendation')
        ->assertSee('Manual Recommendation')
        ->assertSee('System-Generated Recommendation')
        ->assertSee('Added Recommendations')
        ->assertSee('Recommendation Details')
        ->assertSee('Delete Recommendation')
        ->assertDontSee('<option value="0">All facilities</option>', escape: false);

    $this->actingAs($admin)
        ->from(route('modules.energy-conservation.feature', ['feature' => 'energy-saving-tips']))
        ->post(route('modules.energy-conservation.tips.review'), [
            'facility_id' => $facility->id,
            'period' => '2026-07',
            'status' => 'approved',
            'engineer_recommendation' => 'This should not be assigned to an administrator.',
            'assigned_to' => $admin->id,
            'implementation_status' => 'pending',
        ])
        ->assertSessionHasErrors('assigned_to');

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

    $this->assertDatabaseHas('energy_saving_recommendations', [
        'facility_id' => $facility->id,
        'year' => 2026,
        'month' => 7,
        'assigned_to' => $staff->id,
        'implementation_status' => 'in_progress',
        'actual_savings_kwh' => 42.5,
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
        'implementation_status' => 'implemented',
    ]);

    $this->actingAs($admin)
        ->get(route('facilities.monthly-records', [
            'facility' => $facility->id,
            'year' => 2026,
        ]))
        ->assertOk()
        ->assertSee('Implemented')
        ->assertSee('Updated action from the recommendation details modal.');

    $this->actingAs($admin)
        ->delete(route('modules.energy-conservation.tips.destroy', $addedRecommendation))
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->assertDatabaseMissing('energy_saving_recommendations', [
        'id' => $addedRecommendation->id,
    ]);
});
