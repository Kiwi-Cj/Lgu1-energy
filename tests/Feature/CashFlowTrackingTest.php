<?php

use App\Models\EnergyRecord;
use App\Models\Facility;
use App\Models\FacilityUtilityBudget;
use App\Models\User;

test('authorized user can view cash flow dashboard in pure outflow mode when no budget is enacted', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $facility = Facility::factory()->create([
        'name' => 'Quezon City Hall',
        'baseline_kwh' => 2000,
    ]);

    $this->actingAs($admin)
        ->get(route('modules.cashflow.index', ['facility_id' => $facility->id, 'year' => 2026]))
        ->assertOk()
        ->assertSee('Utility Cash Flow &amp; Outflow Tracking', false)
        ->assertSee('Quezon City Hall')
        ->assertSee('Actual Outflow Mode')
        ->assertSee('YTD Actual Outflow')
        ->assertSee('Average Monthly Outflow')
        ->assertSee('Effective Unit Rate')
        ->assertSee('Projected Year-End Outflow');
});

test('authorized user can save and update annual utility budget for a facility', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $facility = Facility::factory()->create(['name' => 'Novaliches District Hospital']);

    $response = $this->actingAs($admin)
        ->post(route('modules.cashflow.budget.update'), [
            'facility_id' => $facility->id,
            'fiscal_year' => 2026,
            'annual_budget_amount' => 1500000.50,
            'notes' => 'City Ordinance Appropriation No. 2026-05',
        ]);

    $response->assertRedirect(route('modules.cashflow.index', [
        'facility_id' => $facility->id,
        'year' => 2026,
    ]));

    $budget = FacilityUtilityBudget::where('facility_id', $facility->id)
        ->where('fiscal_year', 2026)
        ->first();

    expect($budget)->not->toBeNull()
        ->and((float) $budget->annual_budget_amount)->toBe(1500000.50)
        ->and($budget->notes)->toBe('City Ordinance Appropriation No. 2026-05')
        ->and($budget->monthly_budget)->toBe(125000.04);
});

test('authorized user can clear utility budget to return to pure outflow mode', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $facility = Facility::factory()->create(['name' => 'Tatalon Health Center']);

    FacilityUtilityBudget::create([
        'facility_id' => $facility->id,
        'fiscal_year' => 2026,
        'annual_budget_amount' => 500000,
        'created_by' => $admin->id,
    ]);

    $response = $this->actingAs($admin)
        ->post(route('modules.cashflow.budget.update'), [
            'facility_id' => $facility->id,
            'fiscal_year' => 2026,
            'annual_budget_amount' => 0,
        ]);

    $response->assertRedirect(route('modules.cashflow.index', [
        'facility_id' => $facility->id,
        'year' => 2026,
    ]))->assertSessionHas('success', 'Utility budget cleared. Facility is now in pure Cash Flow mode.');

    $budget = FacilityUtilityBudget::where('facility_id', $facility->id)
        ->where('fiscal_year', 2026)
        ->first();

    expect($budget)->toBeNull();
});

test('cash flow dashboard accurately computes ytd outflow from approved energy records with budget enacted', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $facility = Facility::factory()->create(['name' => 'Amoranto Sports Complex']);

    // Set budget: ₱600,000 / year (₱50,000 / month)
    FacilityUtilityBudget::create([
        'facility_id' => $facility->id,
        'fiscal_year' => 2026,
        'annual_budget_amount' => 600000,
        'created_by' => $admin->id,
    ]);

    // Create 2 approved energy records:
    // Jan 2026: 3,000 kWh @ ₱12/kWh = ₱36,000 (actual cost)
    EnergyRecord::create([
        'facility_id' => $facility->id,
        'year' => 2026,
        'month' => 1,
        'actual_kwh' => 3000,
        'energy_cost' => 36000,
        'rate_per_kwh' => 12.00,
        'baseline_kwh' => 4000,
        'review_status' => 'approved',
    ]);

    // Feb 2026: 3,500 kWh @ ₱12/kWh = ₱42,000 (actual cost)
    EnergyRecord::create([
        'facility_id' => $facility->id,
        'year' => 2026,
        'month' => 2,
        'actual_kwh' => 3500,
        'energy_cost' => 42000,
        'rate_per_kwh' => 12.00,
        'baseline_kwh' => 4000,
        'review_status' => 'approved',
    ]);

    // Unapproved record should NOT be counted in YTD outflow
    EnergyRecord::create([
        'facility_id' => $facility->id,
        'year' => 2026,
        'month' => 3,
        'actual_kwh' => 5000,
        'energy_cost' => 60000,
        'rate_per_kwh' => 12.00,
        'review_status' => 'pending',
    ]);

    $response = $this->actingAs($admin)
        ->get(route('modules.cashflow.index', ['facility_id' => $facility->id, 'year' => 2026]));

    $response->assertOk();

    // Total YTD Outflow should be 36,000 + 42,000 = 78,000.00
    // Remaining Budget: 600,000 - 78,000 = 522,000.00
    // Savings: (4000-3000)*12 + (4000-3500)*12 = 12,000 + 6,000 = 18,000.00
    $response->assertSee('78,000.00')
        ->assertSee('522,000.00')
        ->assertSee('18,000.00')
        ->assertSee('approved monthly bills paid');
});

test('user can export cash flow statement as csv with budget', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $facility = Facility::factory()->create(['name' => 'Culiat Highschool']);

    FacilityUtilityBudget::create([
        'facility_id' => $facility->id,
        'fiscal_year' => 2026,
        'annual_budget_amount' => 480000,
        'created_by' => $admin->id,
    ]);

    EnergyRecord::create([
        'facility_id' => $facility->id,
        'year' => 2026,
        'month' => 1,
        'actual_kwh' => 2500,
        'energy_cost' => 30000,
        'rate_per_kwh' => 12.00,
        'baseline_kwh' => 3000,
        'review_status' => 'approved',
    ]);

    $authResponse = $this->actingAs($admin)
        ->postJson(route('downloads.authorize'), [
            'download_password' => 'password',
            'target' => route('modules.cashflow.export', ['facility_id' => $facility->id, 'year' => 2026]),
        ]);

    $authResponse->assertOk()->assertJson(['success' => true]);
    $redirectUrl = $authResponse->json('redirect_url');

    $response = $this->actingAs($admin)->get($redirectUrl);

    $response->assertOk()
        ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

    $content = $response->streamedContent();
    expect($content)->toContain('LGU UTILITY CASH FLOW & BUDGET STATEMENT')
        ->toContain('Culiat Highschool')
        ->toContain('480,000.00')
        ->toContain('30,000.00')
        ->toContain('Jan');
});

test('user can export pure cash flow statement as csv without budget', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $facility = Facility::factory()->create(['name' => 'San Bartolome High School']);

    EnergyRecord::create([
        'facility_id' => $facility->id,
        'year' => 2026,
        'month' => 1,
        'actual_kwh' => 2000,
        'energy_cost' => 24000,
        'rate_per_kwh' => 12.00,
        'review_status' => 'approved',
    ]);

    $authResponse = $this->actingAs($admin)
        ->postJson(route('downloads.authorize'), [
            'download_password' => 'password',
            'target' => route('modules.cashflow.export', ['facility_id' => $facility->id, 'year' => 2026]),
        ]);

    $authResponse->assertOk()->assertJson(['success' => true]);
    $redirectUrl = $authResponse->json('redirect_url');

    $response = $this->actingAs($admin)->get($redirectUrl);

    $response->assertOk()
        ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

    $content = $response->streamedContent();
    expect($content)->toContain('LGU UTILITY CASH FLOW STATEMENT')
        ->toContain('San Bartolome High School')
        ->toContain('24,000.00')
        ->toContain('Jan');
});

test('cash flow dashboard supports separate local and cprf scopes', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $localFac = Facility::factory()->create([
        'name' => 'Local Barangay Hall',
        'source' => 'local',
        'baseline_kwh' => 1000,
    ]);

    $cprfFac = Facility::factory()->create([
        'name' => 'CPRF Integrated Clinic',
        'source' => 'cprf',
        'baseline_kwh' => 2000,
    ]);

    // Query Local scope
    $this->actingAs($admin)
        ->get(route('modules.cashflow.index', ['facility_id' => 'local', 'year' => 2026]))
        ->assertOk()
        ->assertSee('All Local Facilities (Aggregated)')
        ->assertSee('Local Facilities');

    // Query CPRF scope
    $this->actingAs($admin)
        ->get(route('modules.cashflow.index', ['facility_id' => 'cprf', 'year' => 2026]))
        ->assertOk()
        ->assertSee('All CPRF Integrated Facilities (Aggregated)')
        ->assertSee('CPRF Integrated');
});
