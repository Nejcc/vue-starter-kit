<?php

declare(strict_types=1);

namespace App\Contracts\Services;

use Illuminate\Http\Request;

interface AdminNotificationServiceInterface
{
    /** @return array<string, mixed> */
    public function getIndexData(Request $request): array;

    /**
     * @param  array<string, mixed>  $validated
     */
    public function sendNotification(array $validated): int;

    public function markAsRead(string $id): void;

    public function deleteNotification(string $id): void;

    public function deleteAll(?string $filter): int;
}
