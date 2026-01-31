<?php

declare(strict_types=1);

namespace App\Contracts\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface NotificationServiceInterface
{
    /**
     * Get all notifications for a user, paginated.
     *
     * @param  string|null  $filter  'read', 'unread', or null for all
     */
    public function getAllPaginated(User $user, ?string $filter = null, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get recent notifications for dropdown display.
     */
    public function getRecent(User $user, int $limit = 5): Collection;

    /**
     * Get the count of unread notifications for a user.
     */
    public function getUnreadCount(User $user): int;

    /**
     * Mark a specific notification as read.
     */
    public function markAsRead(User $user, string $notificationId): bool;

    /**
     * Mark all notifications as read for a user.
     */
    public function markAllAsRead(User $user): void;

    /**
     * Delete a specific notification.
     */
    public function delete(User $user, string $notificationId): bool;
}
