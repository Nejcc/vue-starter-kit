<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\AdminNotificationRepositoryInterface;
use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Pagination\LengthAwarePaginator;

final class AdminNotificationRepository extends AbstractNonModelRepository implements AdminNotificationRepositoryInterface
{
    public function getPaginated(?string $search, ?string $filter, ?string $userId, int $perPage = 20): LengthAwarePaginator
    {
        $query = DatabaseNotification::query()
            ->with('notifiable')
            ->latest();

        if ($filter) {
            if ($filter === 'unread') {
                $query->whereNull('read_at');
            } elseif ($filter === 'read') {
                $query->whereNotNull('read_at');
            }
        }

        if ($search) {
            $query->where(function ($q) use ($search): void {
                $q->where('data', 'like', "%{$search}%")
                    ->orWhereHasMorph('notifiable', [User::class], function ($q) use ($search): void {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($userId) {
            $query->where('notifiable_type', (new User())->getMorphClass())
                ->where('notifiable_id', $userId);
        }

        return $query->paginate($perPage);
    }

    /** @return array{total: int, unread: int, read: int} */
    public function getStats(): array
    {
        return [
            'total' => DatabaseNotification::count(),
            'unread' => DatabaseNotification::whereNull('read_at')->count(),
            'read' => DatabaseNotification::whereNotNull('read_at')->count(),
        ];
    }

    public function findOrFail(string $id): DatabaseNotification
    {
        return DatabaseNotification::findOrFail($id);
    }

    public function markAsRead(string $id): void
    {
        $this->findOrFail($id)->markAsRead();
    }

    public function delete(string $id): void
    {
        $this->findOrFail($id)->delete();
    }

    public function deleteFiltered(?string $filter): int
    {
        $query = DatabaseNotification::query();

        if ($filter === 'read') {
            $query->whereNotNull('read_at');
        }

        $count = $query->count();
        $query->delete();

        return $count;
    }
}
