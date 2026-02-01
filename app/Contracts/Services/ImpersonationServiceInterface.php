<?php

declare(strict_types=1);

namespace App\Contracts\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

interface ImpersonationServiceInterface
{
    public function canImpersonate(User $user): bool;

    /**
     * @return array{success: bool, error?: string, user?: User}
     */
    public function startImpersonation(User $impersonator, int $targetUserId, Request $request): array;

    /**
     * @return array{success: bool, error?: string, impersonator?: User, logout?: bool}
     */
    public function stopImpersonation(Request $request): array;

    public function isImpersonating(Request $request): bool;

    public function getImpersonator(Request $request): ?User;

    public function getUsersForImpersonation(int $currentUserId, int $limit = 50): Collection;

    public function searchUsers(string $search, int $limit = 50): Collection;
}
