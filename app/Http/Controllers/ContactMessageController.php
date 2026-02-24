<?php

namespace App\Http\Controllers;

use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use App\Models\User;
use App\Support\RoleAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

class ContactMessageController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['nullable', 'string', 'max:150'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $supportEmail = (string) env('ADMIN_SUPPORT_EMAIL', 'energyconservemgmt@gmail.com');
        $authUser = $request->user();

        $senderName = trim((string) (
            $authUser?->full_name
            ?? $authUser?->name
            ?? $authUser?->username
            ?? $validated['name']
        ));
        $senderEmail = (string) ($authUser?->email ?: $validated['email']);

        $contactMessage = ContactMessage::create([
            'name' => $senderName !== '' ? $senderName : $validated['name'],
            'email' => $senderEmail !== '' ? $senderEmail : $validated['email'],
            'subject' => $validated['subject'] ?: null,
            'message' => $validated['message'],
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'mailed_to' => $supportEmail !== '' ? $supportEmail : null,
        ]);

        $this->notifyContactInboxRecipients($contactMessage);

        try {
            if ($supportEmail !== '') {
                Mail::to($supportEmail)->send(new ContactMessageReceived($contactMessage));

                $contactMessage->forceFill([
                    'emailed_at' => now(),
                    'email_error' => null,
                ])->save();
            }

            return back()
                ->withFragment('contact')
                ->with('contact_success', 'Your message has been sent. We will get back to you soon.');
        } catch (\Throwable $e) {
            report($e);

            $contactMessage->forceFill([
                'email_error' => $e->getMessage(),
            ])->save();

            return back()
                ->withFragment('contact')
                ->with('contact_warning', 'Your message was saved, but the email notification could not be sent. Please check mail settings.');
        }
    }

    private function notifyContactInboxRecipients(ContactMessage $contactMessage): void
    {
        try {
            if (! Schema::hasTable('users') || ! Schema::hasTable('notifications')) {
                return;
            }

            $senderName = trim((string) ($contactMessage->name ?: 'Website visitor'));
            $senderEmail = trim((string) ($contactMessage->email ?: ''));
            $subject = trim((string) ($contactMessage->subject ?: ''));

            $message = "New contact message from {$senderName}";
            if ($senderEmail !== '') {
                $message .= " ({$senderEmail})";
            }
            $message .= $subject !== '' ? " - Subject: {$subject}" : ' - No subject';

            $recipients = User::query()
                ->get()
                ->filter(fn (User $user) => RoleAccess::in($user, ['super_admin', 'admin']));

            foreach ($recipients as $recipient) {
                $recipient->notifications()->create([
                    'title' => 'Contact Inbox',
                    'message' => $message,
                    'type' => 'contact',
                ]);
            }
        } catch (\Throwable $e) {
            // Do not break contact form submission if notification creation fails.
        }
    }
}
