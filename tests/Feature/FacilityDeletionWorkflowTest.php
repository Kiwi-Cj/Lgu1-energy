<?php

namespace Tests\Feature;

use App\Models\EnergyRecord;
use App\Models\Facility;
use App\Models\FacilityMeter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FacilityDeletionWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'super admin',
        ]);

        $this->staff = User::factory()->create([
            'role' => 'staff',
        ]);
    }

    public function test_authorized_user_can_move_facility_to_archive_with_reason(): void
    {
        $facility = Facility::create([
            'name' => 'Barangay Health Center',
            'type' => 'Health Facility',
            'address' => '456 Public Rd',
            'barangay' => 'Culiat',
            'status' => 'active',
            'source' => 'local',
        ]);

        $response = $this->actingAs($this->admin)->delete("/facilities/{$facility->id}", [
            'archive_reason' => 'Decommissioned facility',
        ]);

        $response->assertRedirect(route('facilities.index'));
        $response->assertSessionHas('success');

        $this->assertSoftDeleted('facilities', [
            'id' => $facility->id,
            'archive_reason' => 'Decommissioned facility',
            'deleted_by' => $this->admin->id,
        ]);
    }

    public function test_facility_archive_via_modules_alias_route_works(): void
    {
        $facility = Facility::create([
            'name' => 'Old Community Center',
            'type' => 'Community Center',
            'address' => '789 Civic Center Way',
            'barangay' => 'Culiat',
            'status' => 'active',
            'source' => 'local',
        ]);

        $response = $this->actingAs($this->admin)->delete("/modules/facilities/{$facility->id}", [
            'archive_reason' => 'Replaced by new building',
        ]);

        $response->assertRedirect(route('facilities.index'));
        $response->assertSessionHas('success');

        $this->assertSoftDeleted('facilities', [
            'id' => $facility->id,
        ]);
    }

    public function test_archive_reason_is_required_to_delete_facility(): void
    {
        $facility = Facility::create([
            'name' => 'Active Facility',
            'type' => 'Office',
            'address' => '101 Main St',
            'barangay' => 'Culiat',
            'status' => 'active',
            'source' => 'local',
        ]);

        $response = $this->actingAs($this->admin)->delete("/facilities/{$facility->id}", [
            'archive_reason' => '',
        ]);

        $response->assertSessionHas('error');
        $this->assertNotSoftDeleted('facilities', ['id' => $facility->id]);
    }

    public function test_staff_cannot_delete_or_archive_facility(): void
    {
        $facility = Facility::create([
            'name' => 'Protected Facility',
            'type' => 'Office',
            'address' => '101 Main St',
            'barangay' => 'Culiat',
            'status' => 'active',
            'source' => 'local',
        ]);

        $response = $this->actingAs($this->staff)->delete("/facilities/{$facility->id}", [
            'archive_reason' => 'Unauthorized deletion attempt',
        ]);

        $response->assertSessionHas('error');
        $this->assertNotSoftDeleted('facilities', ['id' => $facility->id]);
    }

    public function test_cprf_managed_facility_cannot_be_archived_locally(): void
    {
        $facility = Facility::create([
            'name' => 'CPRF Mirrored Court',
            'type' => 'Covered Court',
            'address' => 'CPRF Complex',
            'barangay' => 'Culiat',
            'status' => 'active',
            'source' => 'cprf',
            'external_ref' => 'cprf-court-1',
        ]);

        $response = $this->actingAs($this->admin)->delete("/facilities/{$facility->id}", [
            'archive_reason' => 'Attempt to delete CPRF facility',
        ]);

        $response->assertSessionHas('error');
        $this->assertNotSoftDeleted('facilities', ['id' => $facility->id]);
    }

    public function test_archived_facility_can_be_restored(): void
    {
        $facility = Facility::create([
            'name' => 'Temporarily Closed Center',
            'type' => 'Recreation',
            'address' => '22 Park Ave',
            'barangay' => 'Culiat',
            'status' => 'active',
            'source' => 'local',
            'archive_reason' => 'Temporary renovation closure',
            'deleted_by' => $this->admin->id,
        ]);
        $facility->delete();

        $this->assertSoftDeleted('facilities', ['id' => $facility->id]);

        $response = $this->actingAs($this->admin)->post("/modules/facilities/{$facility->id}/restore");

        $response->assertRedirect(route('modules.facilities.archive'));
        $response->assertSessionHas('success');

        $this->assertNotSoftDeleted('facilities', ['id' => $facility->id]);
    }

    public function test_archived_facility_can_be_permanently_deleted_with_related_data(): void
    {
        $facility = Facility::create([
            'name' => 'Demolished Facility',
            'type' => 'Warehouse',
            'address' => '99 Storage Rd',
            'barangay' => 'Culiat',
            'status' => 'inactive',
            'source' => 'local',
            'archive_reason' => 'Demolished in 2026',
            'deleted_by' => $this->admin->id,
        ]);

        $meter = FacilityMeter::create([
            'facility_id' => $facility->id,
            'meter_name' => 'Main Meter 1',
            'meter_type' => 'main',
            'status' => 'active',
            'baseline_kwh' => 500,
        ]);

        $record = EnergyRecord::create([
            'facility_id' => $facility->id,
            'meter_id' => $meter->id,
            'year' => 2026,
            'month' => 5,
            'day' => 1,
            'actual_kwh' => 450,
            'rate_per_kwh' => 12,
            'energy_cost' => 5400,
            'recorded_by' => $this->admin->id,
            'input_source' => 'manual',
        ]);

        $facility->delete();

        $response = $this->actingAs($this->admin)->delete("/modules/facilities/{$facility->id}/force-delete");

        $response->assertRedirect(route('modules.facilities.archive'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('facilities', ['id' => $facility->id]);
        $this->assertDatabaseMissing('facility_meters', ['id' => $meter->id]);
        $this->assertDatabaseMissing('energy_records', ['id' => $record->id]);
    }

    public function test_authorized_user_can_view_inactive_facilities_page(): void
    {
        $activeFacility = Facility::create([
            'name' => 'Operational Gym',
            'type' => 'Gymnasium',
            'address' => '100 Active Way',
            'barangay' => 'Culiat',
            'status' => 'active',
            'source' => 'local',
        ]);

        $inactiveFacility = Facility::create([
            'name' => 'Closed Daycare',
            'type' => 'Daycare',
            'address' => '200 Inactive Way',
            'barangay' => 'Culiat',
            'status' => 'inactive',
            'source' => 'local',
        ]);

        $response = $this->actingAs($this->admin)->get(route('facilities.inactive'));
        $response->assertOk();
        $response->assertSee('Inactive Facilities');
        $response->assertSee('Closed Daycare');
        $response->assertDontSee('Operational Gym');

        $aliasResponse = $this->actingAs($this->admin)->get(route('modules.facilities.inactive'));
        $aliasResponse->assertOk();
    }

    public function test_authorized_user_can_deactivate_facility_to_inactive_section(): void
    {
        $facility = Facility::create([
            'name' => 'Renovating Multi-purpose Hall',
            'type' => 'Hall',
            'address' => '300 Project Rd',
            'barangay' => 'Culiat',
            'status' => 'active',
            'source' => 'local',
        ]);

        $response = $this->actingAs($this->admin)->post("/facilities/{$facility->id}/deactivate", [
            'reason' => 'Temporarily closed for renovation',
        ]);

        $response->assertRedirect(route('modules.facilities.inactive'));
        $response->assertSessionHas('success');

        $facility->refresh();
        $this->assertEquals('inactive', $facility->status);
        $this->assertDatabaseHas('facility_audit_logs', [
            'facility_id' => $facility->id,
            'action' => 'deactivated',
        ]);
    }

    public function test_authorized_user_can_reactivate_inactive_facility(): void
    {
        $facility = Facility::create([
            'name' => 'Reopened Health Station',
            'type' => 'Health Facility',
            'address' => '400 Clinic Ave',
            'barangay' => 'Culiat',
            'status' => 'inactive',
            'source' => 'local',
        ]);

        $response = $this->actingAs($this->admin)->post("/facilities/{$facility->id}/reactivate");

        $response->assertRedirect(route('facilities.index'));
        $response->assertSessionHas('success');

        $facility->refresh();
        $this->assertEquals('active', $facility->status);
        $this->assertDatabaseHas('facility_audit_logs', [
            'facility_id' => $facility->id,
            'action' => 'reactivated',
        ]);
    }

    public function test_staff_cannot_deactivate_or_reactivate_facility(): void
    {
        $facility = Facility::create([
            'name' => 'Protected LGU Office',
            'type' => 'Office',
            'address' => '500 Gov Center',
            'barangay' => 'Culiat',
            'status' => 'active',
            'source' => 'local',
        ]);

        $deactivateResponse = $this->actingAs($this->staff)->post("/facilities/{$facility->id}/deactivate");
        $deactivateResponse->assertSessionHas('error');
        $facility->refresh();
        $this->assertEquals('active', $facility->status);

        $facility->update(['status' => 'inactive']);

        $reactivateResponse = $this->actingAs($this->staff)->post("/facilities/{$facility->id}/reactivate");
        $reactivateResponse->assertSessionHas('error');
        $facility->refresh();
        $this->assertEquals('inactive', $facility->status);
    }

    public function test_cprf_managed_facility_cannot_be_deactivated_locally(): void
    {
        $cprfFacility = Facility::create([
            'name' => 'CPRF Managed Plaza',
            'type' => 'Plaza',
            'address' => 'CPRF Sector',
            'barangay' => 'Culiat',
            'status' => 'active',
            'source' => 'cprf',
            'external_ref' => 'cprf-plaza-1',
        ]);

        $response = $this->actingAs($this->admin)->post("/facilities/{$cprfFacility->id}/deactivate");
        $response->assertSessionHas('error');

        $cprfFacility->refresh();
        $this->assertEquals('active', $cprfFacility->status);
    }
}
