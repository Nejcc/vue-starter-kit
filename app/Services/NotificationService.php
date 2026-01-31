<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Services\NotificationServiceInterface;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

final class NotificationService implements NotificationServiceInterface
{
    public function getAllPaginated(User $user, ?string $filter = null, int $perPage = 15): LengthAwarePaginator
    {
        $query = $user->notifications();

        if ($filter === 'unread') {
            $query->whereNull('read_at');
        } elseif ($filter === 'read') {
            $query->whereNotNull('read_at');
        }

        return $query->paginate($perPage);
    }

    public function getRecent(User $user, int $limit = 5): Collection
    {
        return $user->notifications()->limit($limit)->get();
    }

    public function getUnreadCount(User $user): int
    {
        return $user->unreadNotifications()->count();
    }

    public function markAsRead(User $user, string $notificationId): bool
    {
        $notification = $user->notifications()->where('id', $notificationId)->first();

        if (!$notification) {
            return false;
        }

        $notification->markAsRead();

        return true;
    }

    public function markAllAsRead(User $user): void
    {
        $user->unreadNotifications->markAsRead();
    }

    public function delete(User $user, string $notificationId): bool
    {
        $notification = $user->notifications()->where('id', $notificationId)->first();

        if (!$notification) {
            return false;
        }

        $notification->delete();

        return true;
    }
}
