<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\GeneralNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Inertia\Inertia;
use Inertia\Response;

final class NotificationsController extends Controller
{
    public function index(Request $request): Response
    {
        $query = DatabaseNotification::query()
            ->with('notifiable')
            ->latest();

        if ($filter = $request->get('filter')) {
            if ($filter === 'unread') {
                $query->whereNull('read_at');
            } elseif ($filter === 'read') {
                $query->whereNotNull('read_at');
            }
        }

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search): void {
                $q->where('data', 'like', "%{$search}%")
                    ->orWhereHasMorph('notifiable', [User::class], function ($q) use ($search): void {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($userId = $request->get('user_id')) {
            $query->where('notifiable_type', (new User())->getMorphClass())
                ->where('notifiable_id', $userId);
        }

        $notifications = $query->paginate(20)->through(fn (DatabaseNotification $notification) => [
            'id' => $notification->id,
            'type' => class_basename($notification->type),
            'data' => $notification->data,
            'read_at' => $notification->read_at?->toISOString(),
            'created_at' => $notification->created_at->toISOString(),
            'notifiable' => $notification->notifiable ? [
                'id' => $notification->notifiable->id,
                'name' => $notification->notifiable->name ?? 'Unknown',
                'email' => $notification->notifiable->email ?? '',
            ] : null,
        ]);

        $users = User::query()
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        $stats = [
            'total' => DatabaseNotification::count(),
            'unread' => DatabaseNotification::whereNull('read_at')->count(),
            'read' => DatabaseNotification::whereNotNull('read_at')->count(),
        ];

        return Inertia::render('admin/Notifications/Index', [
            'notifications' => $notifications,
            'users' => $users,
            'stats' => $stats,
            'filters' => [
                'search' => $request->get('search', ''),
                'filter' => $request->get('filter', 'all'),
                'user_id' => $request->get('user_id', ''),
            ],
        ]);
    }

    public function send(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['required', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:1000'],
            'action_url' => ['nullable', 'string', 'max:500'],
        ]);

        $users = User::whereIn('id', $validated['user_ids'])->get();

        foreach ($users as $user) {
            $user->notify(new GeneralNotification(
                title: $validated['title'],
                body: $validated['body'],
                actionUrl: $validated['action_url'] ?? null,
            ));
        }

        return back()->with('success', 'Notification sent to ' . $users->count() . ' user(s).');
    }

    public function destroy(string $id): RedirectResponse
    {
        $notification = DatabaseNotification::findOrFail($id);
        $notification->delete();

        return back()->with('success', 'Notification deleted.');
    }

    public function markAsRead(string $id): RedirectResponse
    {
        $notification = DatabaseNotification::findOrFail($id);
        $notification->markAsRead();

        return back();
    }

    public function destroyAll(Request $request): RedirectResponse
    {
        $query = DatabaseNotification::query();

        if ($filter = $request->get('filter')) {
            if ($filter === 'read') {
                $query->whereNotNull('read_at');
            }
        }

        $count = $query->count();
        $query->delete();

        return back()->with('success', "Deleted {$count} notification(s).");
    }
}
