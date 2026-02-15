<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use Illuminate\Support\Collection;

interface SessionRepositoryInterface
{
    /** @return Collection<int, object> */
    public function getUserSessions(int $userId): Collection;

    public function deleteSession(string $sessionId, int $userId): void;

    public function deleteAllExceptCurrent(int $userId, string $currentSessionId): void;
}
