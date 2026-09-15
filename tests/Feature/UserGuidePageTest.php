<?php

use App\Models\User;

test('guest can access bilingual user guide page', function () {
    $response = $this->get('/user-guide');
    $response->assertOk();
    $response->assertSee('Gabay sa Paggamit');
    $response->assertSee('User Guide &amp; Manual', false);
    $response->assertSee('data-guide-lang', false);
});

test('authenticated user can access user guide page and modules alias with both languages', function () {
    $user = User::factory()->create(['role' => 'staff']);

    $response = $this->actingAs($user)->get('/user-guide');
    $response->assertOk();
    $response->assertSee('Talaan ng Nilalaman');
    $response->assertSee('Table of Contents');

    $modulesResponse = $this->actingAs($user)->get('/modules/user-guide');
    $modulesResponse->assertOk();
});
