<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\ContactMessageReply;
use App\Support\RoleAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class ContactInboxController extends Controller
{
    private function ensureAdminAccess(Request $request): string
    {
        $role = RoleAccess::normalize($request->user());

        if (! RoleAccess::in($request->user(), ['super_admin', 'admin'])) {
            abort(403, 'Only Admin and Super Admin can access the contact inbox.');
        }

        return $role;
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $role = $this->ensureAdminAccess($request);

        $tab = 'inbox';

        $search = trim((string) $request->query('q', ''));
        $filter = strtolower(trim((string) $request->query('filter', 'all')));
        $allowedFilters = ['all', 'unread', 'replied', 'failed'];
        if (! in_array($filter, $allowedFilters, true)) {
            $filter = 'all';
        }
        $sort = strtolower(trim((string) $request->query('sort', 'latest_activity')));
        if (! in_array($sort, ['latest_activity', 'latest_incoming', 'oldest'], true)) {
            $sort = 'latest_activity';
        }

        $messagesQuery = ContactMessage::query()
            ->withCount('replies')
            ->withMax('replies', 'created_at')
            ->when($filter === 'unread', function ($query) {
                $query->whereNull('read_at');
            })
            ->when($filter === 'replied', function ($query) {
                $query->has('replies');
            })
            ->when($filter === 'failed', function ($query) {
                $query->where(function ($q) {
                    $q->whereNotNull('email_error')
                        ->orWhereHas('replies', function ($replyQuery) {
                            $replyQuery->where('send_status', 'failed');
                        });
                });
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhere('subject', 'like', '%' . $search . '%')
                        ->orWhere('message', 'like', '%' . $search . '%');
                });
            })
            ->when($sort === 'latest_activity', function ($query) {
                $query->orderByRaw('COALESCE(replies_max_created_at, contact_messages.created_at) DESC')
                    ->orderByDesc('contact_messages.created_at');
            })
            ->when($sort === 'latest_incoming', function ($query) {
                $query->orderByDesc('contact_messages.created_at');
            })
            ->when($sort === 'oldest', function ($query) {
                $query->orderBy('contact_messages.created_at');
            });

        $messages = $messagesQuery->paginate(12, ['*'], 'inbox_page')->withQueryString();

        $selectedId = (int) $request->query('message', 0);
        $selectedMessage = null;
        if ($tab === 'inbox') {
            $selectedMessage = $selectedId > 0
                ? ContactMessage::with(['replies.sender', 'readBy'])->find($selectedId)
                : null;

            if (! $selectedMessage && $messages->count() > 0) {
                $selectedMessage = ContactMessage::with(['replies.sender', 'readBy'])->find($messages->first()->id);
            }
        }

        if ($tab === 'inbox' && $selectedMessage && ! $selectedMessage->read_at) {
            $markedAt = now();
            $selectedMessage->forceFill([
                'read_at' => $markedAt,
                'read_by_user_id' => $user?->id,
            ])->save();

            // Keep the currently loaded list item in sync after auto-marking.
            $messages->setCollection(
                $messages->getCollection()->map(function ($message) use ($selectedMessage, $markedAt, $user) {
                    if ((int) $message->id === (int) $selectedMessage->id) {
                        $message->read_at = $markedAt;
                        $message->read_by_user_id = $user?->id;
                    }

                    return $message;
                })
            );
        }

        $sentReplies = ContactMessageReply::query()
            ->with(['contactMessage', 'sender'])
            ->when($search !== '' && $tab === 'sent', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('recipient_email', 'like', '%' . $search . '%')
                        ->orWhere('subject', 'like', '%' . $search . '%')
                        ->orWhere('message', 'like', '%' . $search . '%')
                        ->orWhereHas('contactMessage', function ($messageQuery) use ($search) {
                            $messageQuery->where('name', 'like', '%' . $search . '%')
                                ->orWhere('email', 'like', '%' . $search . '%');
                        });
                });
            })
            ->orderByRaw('COALESCE(sent_at, created_at) DESC')
            ->orderByDesc('id')
            ->paginate(15, ['*'], 'sent_page')
            ->withQueryString();

        $notifications = $user ? $user->notifications()->orderByDesc('created_at')->take(10)->get() : collect();
        $unreadNotifCount = $user ? $user->notifications()->whereNull('read_at')->count() : 0;

        $stats = [
            'total' => ContactMessage::count(),
            'today' => ContactMessage::whereDate('created_at', today())->count(),
            'email_failed' => ContactMessage::whereNotNull('email_error')->count(),
            'unread' => ContactMessage::whereNull('read_at')->count(),
        ];
        $filterCounts = [
            'all' => ContactMessage::count(),
            'unread' => ContactMessage::whereNull('read_at')->count(),
            'replied' => ContactMessage::has('replies')->count(),
            'failed' => ContactMessage::where(function ($q) {
                $q->whereNotNull('email_error')
                    ->orWhereHas('replies', function ($replyQuery) {
                        $replyQuery->where('send_status', 'failed');
                    });
            })->count(),
        ];
        $tabCounts = [
            'inbox' => $filterCounts['all'] ?? ContactMessage::count(),
            'sent' => ContactMessageReply::count(),
        ];

        return view('modules.contact-messages.index', [
            'messages' => $messages,
            'sentReplies' => $sentReplies,
            'selectedMessage' => $selectedMessage,
            'tab' => $tab,
            'search' => $search,
            'filter' => $filter,
            'sort' => $sort,
            'filterCounts' => $filterCounts,
            'tabCounts' => $tabCounts,
            'stats' => $stats,
            'notifications' => $notifications,
            'unreadNotifCount' => $unreadNotifCount,
            'role' => $role,
            'user' => $user,
        ]);
    }

    public function markRead(Request $request, ContactMessage $contactMessage): RedirectResponse
    {
        $this->ensureAdminAccess($request);

        $contactMessage->forceFill([
            'read_at' => now(),
            'read_by_user_id' => $request->user()?->id,
        ])->save();

        return back()->with('inbox_success', 'Message marked as read.');
    }

    public function markUnread(Request $request, ContactMessage $contactMessage): RedirectResponse
    {
        $this->ensureAdminAccess($request);

        $contactMessage->forceFill([
            'read_at' => null,
            'read_by_user_id' => null,
        ])->save();

        return back()->with('inbox_success', 'Message marked as unread.');
    }

    public function reply(Request $request, ContactMessage $contactMessage): RedirectResponse
    {
        $this->ensureAdminAccess($request);

        $validated = $request->validate([
            'reply_subject' => ['required', 'string', 'max:190'],
            'reply_message' => ['required', 'string', 'max:8000'],
            'reply_attachments' => ['nullable', 'array', 'max:5'],
            'reply_attachments.*' => ['file', 'max:5120', 'mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx,csv,txt'],
        ]);

        $attachmentMeta = [];
        $uploadedFiles = $request->file('reply_attachments', []);

        foreach ($uploadedFiles as $file) {
            if (! $file) {
                continue;
            }

            $storedPath = $file->store('contact-replies/' . $contactMessage->id, 'public');

            $attachmentMeta[] = [
                'path' => $storedPath,
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime' => $file->getClientMimeType(),
            ];
        }

        $reply = ContactMessageReply::create([
            'contact_message_id' => $contactMessage->id,
            'sent_by_user_id' => $request->user()?->id,
            'recipient_email' => $contactMessage->email,
            'subject' => $validated['reply_subject'],
            'message' => $validated['reply_message'],
            'attachments' => $attachmentMeta,
            'send_status' => 'pending',
        ]);

        try {
            Mail::raw($validated['reply_message'], function ($mail) use ($contactMessage, $validated, $attachmentMeta) {
                $mail->to($contactMessage->email, $contactMessage->name ?: null)
                    ->subject($validated['reply_subject']);

                foreach ($attachmentMeta as $attachment) {
                    $path = (string) ($attachment['path'] ?? '');
                    if ($path === '' || ! Storage::disk('public')->exists($path)) {
                        continue;
                    }

                    $mail->attach(
                        Storage::disk('public')->path($path),
                        ['as' => (string) ($attachment['original_name'] ?? basename($path))]
                    );
                }
            });

            $reply->forceFill([
                'send_status' => 'sent',
                'sent_at' => now(),
                'error_message' => null,
            ])->save();

            $contactMessage->forceFill([
                'read_at' => $contactMessage->read_at ?: now(),
                'read_by_user_id' => $contactMessage->read_by_user_id ?: ($request->user()?->id),
            ])->save();

            return back()->with('reply_success', 'Reply sent successfully to ' . $contactMessage->email . '.');
        } catch (\Throwable $e) {
            report($e);

            $reply->forceFill([
                'send_status' => 'failed',
                'error_message' => $e->getMessage(),
            ])->save();

            return back()
                ->withInput()
                ->with('reply_error', 'Failed to send reply from the system. Please check mail settings and try again.');
        }
    }
}
