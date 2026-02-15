<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Pagination\LengthAwarePaginator;

interface AdminNotificationRepositoryInterface
{
    public function getPaginated(?string $search, ?string $filter, ?string $userId, int $perPage = 20): LengthAwarePaginator;

    /** @return array{total: int, unread: int, read: int} */
    public function getStats(): array;

    public function findOrFail(string $id): DatabaseNotification;

    public function markAsRead(string $id): void;

    public function delete(string $id): void;

    public function deleteFiltered(?string $filter): int;
}
