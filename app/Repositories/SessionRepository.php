<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\SessionRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class SessionRepository extends AbstractNonModelRepository implements SessionRepositoryInterface
{
    /** @return Collection<int, object> */
    public function getUserSessions(int $userId): Collection
    {
        return DB::table('sessions')
            ->where('user_id', $userId)
            ->orderByDesc('last_activity')
            ->get();
    }

    public function deleteSession(string $sessionId, int $userId): void
    {
        DB::table('sessions')
            ->where('id', $sessionId)
            ->where('user_id', $userId)
            ->delete();
    }

    public function deleteAllExceptCurrent(int $userId, string $currentSessionId): void
    {
        DB::table('sessions')
            ->where('user_id', $userId)
            ->where('id', '!=', $currentSessionId)
            ->delete();
    }
}
