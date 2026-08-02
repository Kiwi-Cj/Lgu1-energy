<?php

use App\Models\ContactMessage;
use App\Models\User;

function contactInboxMessage(array $overrides = []): ContactMessage
{
    return ContactMessage::create(array_merge([
        'name' => 'Inbox Sender',
        'email' => 'sender@example.com',
        'subject' => 'Energy concern',
        'message' => 'Please review this concern.',
    ], $overrides));
}

test('admin can archive and restore a contact message', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $message = contactInboxMessage();

    $this->actingAs($admin)
        ->post(route('modules.contact-messages.archive', $message), [
            'return_filter' => 'all',
            'return_sort' => 'latest_activity',
        ])
        ->assertRedirect(route('modules.contact-messages.index', [
            'filter' => 'all',
            'sort' => 'latest_activity',
        ]));

    expect($message->fresh()->archived_at)->not->toBeNull();

    $this->actingAs($admin)
        ->get(route('modules.contact-messages.index', ['filter' => 'archived']))
        ->assertOk()
        ->assertSee('Energy concern');

    $this->actingAs($admin)
        ->post(route('modules.contact-messages.restore', $message))
        ->assertRedirect(route('modules.contact-messages.index', ['filter' => 'archived']));

    expect($message->fresh()->archived_at)->toBeNull();
});

test('marking a selected message unread returns to the list so it stays unread', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $message = contactInboxMessage([
        'read_at' => now(),
        'read_by_user_id' => $admin->id,
    ]);

    $this->actingAs($admin)
        ->post(route('modules.contact-messages.mark-unread', $message), [
            'return_filter' => 'all',
            'return_sort' => 'latest_activity',
        ])
        ->assertRedirect(route('modules.contact-messages.index', [
            'filter' => 'all',
            'sort' => 'latest_activity',
        ]));

    expect($message->fresh()->read_at)->toBeNull()
        ->and($message->fresh()->read_by_user_id)->toBeNull();
});

test('staff cannot archive contact messages', function () {
    $staff = User::factory()->create(['role' => 'staff']);
    $message = contactInboxMessage();

    $this->actingAs($staff)
        ->post(route('modules.contact-messages.archive', $message))
        ->assertForbidden();

    expect($message->fresh()->archived_at)->toBeNull();
});

test('only super admin can permanently delete an archived contact message', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $superAdmin = User::factory()->create(['role' => 'super_admin']);
    $message = contactInboxMessage(['archived_at' => now()]);

    $this->actingAs($admin)
        ->delete(route('modules.contact-messages.destroy', $message))
        ->assertForbidden();

    $this->assertDatabaseHas('contact_messages', ['id' => $message->id]);

    $this->actingAs($superAdmin)
        ->delete(route('modules.contact-messages.destroy', $message))
        ->assertRedirect(route('modules.contact-messages.index', ['filter' => 'archived']));

    $this->assertDatabaseMissing('contact_messages', ['id' => $message->id]);
});

test('active messages must be archived before permanent deletion', function () {
    $superAdmin = User::factory()->create(['role' => 'super_admin']);
    $message = contactInboxMessage();

    $this->actingAs($superAdmin)
        ->delete(route('modules.contact-messages.destroy', $message))
        ->assertRedirect(route('modules.contact-messages.index'));

    $this->assertDatabaseHas('contact_messages', ['id' => $message->id]);
});
