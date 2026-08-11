<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $filter = strtolower((string) $request->query('filter', 'all'));
        if (! in_array($filter, ['all', 'unread', 'critical', 'unacknowledged', 'acknowledged'], true)) {
            $filter = 'all';
        }

        $oneMonthNotifications = $request->user()
            ->notifications()
            ->where('created_at', '>=', now()->subMonth());

        $highOrAbove = function ($query): void {
            $query->where(function ($query) {
                $query->whereRaw('LOWER(message) LIKE ?', ['%critical%'])
                    ->orWhereRaw('LOWER(message) LIKE ?', ['%very high%'])
                    ->orWhereRaw('LOWER(message) LIKE ?', ['%high%']);
            });
        };

        $notificationSummary = [
            'total' => (clone $oneMonthNotifications)->count(),
            'unread' => (clone $oneMonthNotifications)->whereNull('read_at')->count(),
            'high' => (clone $oneMonthNotifications)->tap($highOrAbove)->count(),
            'read' => (clone $oneMonthNotifications)->whereNotNull('read_at')->count(),
            'acknowledged' => (clone $oneMonthNotifications)->whereNotNull('acknowledged_at')->count(),
        ];

        $notifications = $oneMonthNotifications
            ->when($filter === 'unread', fn ($query) => $query->whereNull('read_at'))
            ->when($filter === 'critical', $highOrAbove)
            ->when($filter === 'unacknowledged', fn ($query) => $query->whereNull('acknowledged_at'))
            ->when($filter === 'acknowledged', fn ($query) => $query->whereNotNull('acknowledged_at'))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('notifications.index', compact('notifications', 'filter', 'notificationSummary'));
    }

    public function markAsRead(Request $request, Notification $notification)
    {
        $user = Auth::user();

        if (! $user || (int) $notification->user_id !== (int) $user->id) {
            abort(403);
        }

        if ($notification->read_at === null) {
            $notification->update(['read_at' => now()]);
        }

        return response()->json(['success' => true]);
    }

    public function markAllAsRead(Request $request)
    {
        $user = Auth::user();
        Notification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
        return response()->json(['success' => true]);
    }

    public function acknowledge(Request $request, Notification $notification)
    {
        $user = $request->user();
        if (! $user || (int) $notification->user_id !== (int) $user->id) {
            abort(403);
        }

        $notification->update([
            'read_at' => $notification->read_at ?? now(),
            'acknowledged_at' => $notification->acknowledged_at ?? now(),
        ]);

        return response()->json([
            'success' => true,
            'acknowledged_at' => $notification->acknowledged_at?->toIso8601String(),
        ]);
    }
}
