<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Repositories\SessionRepositoryInterface;
use App\Contracts\Services\SessionManagementServiceInterface;
use App\Support\UserAgentParser;
use Illuminate\Support\Carbon;

final class SessionManagementService extends AbstractNonModelService implements SessionManagementServiceInterface
{
    public function __construct(
        private readonly SessionRepositoryInterface $repository,
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getUserSessions(int $userId, string $currentSessionId): array
    {
        return $this->repository->getUserSessions($userId)
            ->map(function (object $session) use ($currentSessionId): array {
                $device = UserAgentParser::parse($session->user_agent);

                return [
                    'id' => $session->id,
                    'ip_address' => $session->ip_address,
                    'is_current' => $session->id === $currentSessionId,
                    'last_active' => Carbon::createFromTimestamp($session->last_activity)->diffForHumans(),
                    'last_active_at' => Carbon::createFromTimestamp($session->last_activity)->toIso8601String(),
                    'device' => $device,
                ];
            })->all();
    }

    public function revokeSession(string $sessionId, int $userId): void
    {
        $this->repository->deleteSession($sessionId, $userId);
    }

    public function revokeAllOtherSessions(int $userId, string $currentSessionId): void
    {
        $this->repository->deleteAllExceptCurrent($userId, $currentSessionId);
    }
}
