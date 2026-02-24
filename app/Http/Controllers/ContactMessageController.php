<?php

namespace App\Http\Controllers;

use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

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

        $contactMessage = ContactMessage::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'subject' => $validated['subject'] ?: null,
            'message' => $validated['message'],
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'mailed_to' => $supportEmail !== '' ? $supportEmail : null,
        ]);

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
}
