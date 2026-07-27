<?php

use App\Mail\UserWelcome;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

test('super admin can create a user from user management', function () {
    Mail::fake();
    $superAdmin = User::factory()->create(['role' => 'super_admin']);

    $response = $this->actingAs($superAdmin)->post(route('users.store'), [
        'full_name' => 'Created Staff',
        'email' => 'created.staff@example.test',
        'username' => 'created.staff',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'staff',
        'status' => 'active',
        'department' => 'Operations',
        'contact_number' => '09123456789',
    ]);

    $response
        ->assertRedirect(route('users.index', absolute: false))
        ->assertSessionHas('success', 'User created successfully! A welcome email was sent to the user.');

    $this->assertDatabaseHas('users', [
        'full_name' => 'Created Staff',
        'name' => 'Created Staff',
        'email' => 'created.staff@example.test',
        'username' => 'created.staff',
        'role' => 'staff',
        'status' => 'active',
    ]);

    Mail::assertSent(UserWelcome::class, function (UserWelcome $mail) {
        return $mail->hasTo('created.staff@example.test')
            && $mail->recipientName === 'Created Staff'
            && $mail->role === 'staff'
            && $mail->temporaryPassword === 'password123'
            && $mail->loginUrl === route('login');
    });
});

test('create user validation errors use the create user error bag', function () {
    Mail::fake();
    $superAdmin = User::factory()->create(['role' => 'super_admin']);
    $existingUser = User::factory()->create();

    $response = $this
        ->from(route('users.index'))
        ->actingAs($superAdmin)
        ->post(route('users.store'), [
            'full_name' => 'Duplicate Email',
            'email' => $existingUser->email,
            'username' => 'duplicate.email',
            'password' => 'password123',
            'password_confirmation' => 'different-password',
            'role' => 'staff',
            'status' => 'active',
        ]);

    $response
        ->assertRedirect(route('users.index', absolute: false))
        ->assertSessionHasErrorsIn('createUser', ['email', 'password'])
        ->assertSessionHasInput('full_name', 'Duplicate Email');

    Mail::assertNothingSent();
});

test('create user modal displays validation errors and restores submitted fields', function () {
    $superAdmin = User::factory()->create(['role' => 'super_admin']);

    $response = $this
        ->from(route('users.index'))
        ->actingAs($superAdmin)
        ->followingRedirects()
        ->post(route('users.store'), [
            'full_name' => 'Retry User',
            'email' => 'not-an-email',
            'username' => 'retry.user',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'staff',
            'status' => 'active',
        ]);

    $response
        ->assertOk()
        ->assertSee('Please correct the following:')
        ->assertSee('The email field must be a valid email address.')
        ->assertSee('Retry User')
        ->assertSee('openUserModalCreate();', escape: false);
});

test('super admin can update a user from user management', function () {
    $superAdmin = User::factory()->create(['role' => 'super_admin']);
    $staff = User::factory()->create([
        'full_name' => 'Original Name',
        'name' => 'Original Name',
        'role' => 'staff',
    ]);

    $response = $this->actingAs($superAdmin)->put(route('users.update', $staff->id), [
        'editing_user_id' => $staff->id,
        'full_name' => 'Updated Name',
        'email' => $staff->email,
        'username' => $staff->username,
        'role' => 'energy_officer',
        'status' => 'active',
        'department' => 'Engineering',
        'contact_number' => '09987654321',
    ]);

    $response
        ->assertRedirect(route('users.index', absolute: false))
        ->assertSessionHas('success', 'User updated successfully!');

    $this->assertDatabaseHas('users', [
        'id' => $staff->id,
        'full_name' => 'Updated Name',
        'name' => 'Updated Name',
        'role' => 'energy_officer',
        'department' => 'Engineering',
        'contact_number' => '09987654321',
    ]);
});

test('edit user validation errors use the edit user error bag', function () {
    $superAdmin = User::factory()->create(['role' => 'super_admin']);
    $staff = User::factory()->create(['role' => 'staff']);
    $existingUser = User::factory()->create();

    $response = $this
        ->from(route('users.index'))
        ->actingAs($superAdmin)
        ->put(route('users.update', $staff->id), [
            'editing_user_id' => $staff->id,
            'full_name' => 'Retry Updated User',
            'email' => $existingUser->email,
            'username' => $staff->username,
            'role' => 'staff',
            'status' => 'active',
        ]);

    $response
        ->assertRedirect(route('users.index', absolute: false))
        ->assertSessionHasErrorsIn('editUser', ['email'])
        ->assertSessionHasInput('editing_user_id', (string) $staff->id)
        ->assertSessionHasInput('full_name', 'Retry Updated User');
});

test('edit user modal displays validation errors and restores the edited user', function () {
    $superAdmin = User::factory()->create(['role' => 'super_admin']);
    $staff = User::factory()->create(['role' => 'staff']);

    $response = $this
        ->from(route('users.index'))
        ->actingAs($superAdmin)
        ->followingRedirects()
        ->put(route('users.update', $staff->id), [
            'editing_user_id' => $staff->id,
            'full_name' => 'Retry Updated User',
            'email' => 'not-an-email',
            'username' => $staff->username,
            'role' => 'staff',
            'status' => 'active',
        ]);

    $response
        ->assertOk()
        ->assertSee('Please correct the following:')
        ->assertSee('The email field must be a valid email address.')
        ->assertSee('Retry Updated User')
        ->assertSee('openUserModalEdit(failedEditTrigger);', escape: false);
});
