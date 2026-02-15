<?php

declare(strict_types=1);

namespace App\Contracts\Services;

interface SessionManagementServiceInterface
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function getUserSessions(int $userId, string $currentSessionId): array;

    public function revokeSession(string $sessionId, int $userId): void;

    public function revokeAllOtherSessions(int $userId, string $currentSessionId): void;
}
