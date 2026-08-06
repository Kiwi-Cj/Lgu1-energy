<?php

use App\Models\EnergyRecord;
use App\Models\Facility;
use App\Models\User;

test('review roles can view who submitted the latest monthly records', function (string $role) {
    $reviewer = User::factory()->create(['role' => $role]);
    $staff = User::factory()->create([
        'role' => 'staff',
        'full_name' => 'Maria Santos',
        'name' => 'Maria Santos',
    ]);
    $facility = Facility::factory()->create(['name' => 'City Hall Annex']);

    EnergyRecord::create([
        'facility_id' => $facility->id,
        'year' => 2026,
        'month' => 7,
        'actual_kwh' => 1250,
        'recorded_by' => $staff->id,
        'input_source' => 'manual',
    ]);

    $this->actingAs($reviewer)
        ->get(route('monthly-record-activity.index'))
        ->assertOk()
        ->assertSee('Maria Santos')
        ->assertSee('City Hall Annex')
        ->assertSee('July 2026')
        ->assertSee('Latest');
})->with(['super_admin', 'admin', 'engineer']);

test('staff cannot view reviewer monthly record activity', function () {
    $staff = User::factory()->create(['role' => 'staff']);

    $this->actingAs($staff)
        ->get(route('monthly-record-activity.index'))
        ->assertRedirect(route('dashboard.index'));
});

test('reviewer can approve a monthly record and notify its encoder', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $staff = User::factory()->create(['role' => 'staff']);
    $facility = Facility::factory()->create();
    $record = EnergyRecord::create([
        'facility_id' => $facility->id,
        'year' => 2026,
        'month' => 7,
        'actual_kwh' => 500,
        'recorded_by' => $staff->id,
    ]);

    $this->actingAs($admin)
        ->patch(route('monthly-record-activity.review', $record), [
            'review_status' => 'approved',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('energy_records', [
        'id' => $record->id,
        'review_status' => 'approved',
        'reviewed_by' => $admin->id,
    ]);
    $this->assertDatabaseHas('notifications', [
        'user_id' => $staff->id,
        'type' => 'monthly_record_review',
        'title' => 'Monthly Record Approved',
    ]);
});

test('returning a monthly record requires remarks', function () {
    $engineer = User::factory()->create(['role' => 'engineer']);
    $record = EnergyRecord::create([
        'facility_id' => Facility::factory()->create()->id,
        'year' => 2026,
        'month' => 7,
        'actual_kwh' => 500,
    ]);

    $this->actingAs($engineer)
        ->from(route('monthly-record-activity.index'))
        ->patch(route('monthly-record-activity.review', $record), [
            'review_status' => 'returned',
        ])
        ->assertSessionHasErrors('review_remarks');
});

test('a monthly record submission notifies engineer admin and super admin', function () {
    $staff = User::factory()->create([
        'role' => 'staff',
        'full_name' => 'Juan Dela Cruz',
        'name' => 'Juan Dela Cruz',
    ]);
    $engineer = User::factory()->create(['role' => 'engineer']);
    $admin = User::factory()->create(['role' => 'admin']);
    $superAdmin = User::factory()->create(['role' => 'super_admin']);
    $facility = Facility::factory()->create(['name' => 'Public Library']);

    EnergyRecord::create([
        'facility_id' => $facility->id,
        'year' => 2026,
        'month' => 7,
        'actual_kwh' => 875,
        'recorded_by' => $staff->id,
        'input_source' => 'manual',
    ]);

    foreach ([$engineer, $admin, $superAdmin] as $reviewer) {
        $this->assertDatabaseHas('notifications', [
            'user_id' => $reviewer->id,
            'type' => 'monthly_record_submission',
            'title' => 'New Monthly Record',
        ]);
    }
});

test('integrated records show the external encoder and source', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $facility = Facility::factory()->create(['name' => 'Sports Complex']);

    EnergyRecord::create([
        'facility_id' => $facility->id,
        'year' => 2026,
        'month' => 6,
        'actual_kwh' => 4200,
        'recorded_by_name' => 'CPRF Staff Account',
        'input_source' => 'cprf',
    ]);

    $this->actingAs($admin)
        ->get(route('monthly-record-activity.index', ['source' => 'cprf']))
        ->assertOk()
        ->assertSee('CPRF Staff Account')
        ->assertSee('CPRF Integration');
});

test('the removed cprf reading endpoint cannot create an energy record', function () {
    config(['services.cprf_integration.token' => 'test-token']);

    $facility = Facility::factory()->create(['name' => 'Integrated Civic Center']);

    $this->withToken('test-token')
        ->postJson('/api/v1/cprf/facility-readings', [
            'facility_id' => $facility->id,
            'year' => 2026,
            'month' => 8,
            'previous_reading_kwh' => 1000,
            'current_reading_kwh' => 1275,
            'reading_date' => '2026-08-30',
            'recorded_by_name' => 'CPRF Monthly Encoder',
        ])
        ->assertNotFound();

    expect(EnergyRecord::query()->where('facility_id', $facility->id)->exists())->toBeFalse();
});
