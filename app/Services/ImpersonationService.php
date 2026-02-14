<?php

declare(strict_types=1);

namespace App\Services;

use App\Constants\AuditEvent;
use App\Contracts\Services\ImpersonationServiceInterface;
use App\Contracts\Services\UserServiceInterface;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Impersonation service implementation.
 *
 * Provides business logic for user impersonation functionality.
 * Handles session management, authorization, and audit logging.
 */
final class ImpersonationService implements ImpersonationServiceInterface
{
    /**
     * Create a new impersonation service instance.
     */
    public function __construct(
        private readonly UserServiceInterface $userService
    ) {}

    /**
     * Check if the current user can impersonate.
     *
     * @param  User  $user  The user to check
     * @return bool True if the user can impersonate
     */
    public function canImpersonate(User $user): bool
    {
        // Check if user is super-admin, admin, or has impersonate permission
        return $user->hasAnyRole(['super-admin', 'admin']) || $user->can('impersonate');
    }

    /**
     * Start impersonating a user.
     *
     * @param  User  $impersonator  The user initiating impersonation
     * @param  int  $targetUserId  The ID of the user to impersonate
     * @param  Request  $request  The current request
     * @return array{success: bool, user?: User, error?: string}
     */
    public function startImpersonation(User $impersonator, int $targetUserId, Request $request): array
    {
        $targetUser = $this->userService->findById($targetUserId);

        if (!$targetUser) {
            return [
                'success' => false,
                'error' => 'User not found.',
            ];
        }

        // Prevent impersonating yourself
        if ($targetUser->id === $impersonator->id) {
            return [
                'success' => false,
                'error' => 'You cannot impersonate yourself.',
            ];
        }

        // Store the original user ID in session
        $request->session()->put('impersonator_id', $impersonator->id);

        // Log the impersonation start
        AuditLog::log(
            event: AuditEvent::IMPERSONATION_STARTED,
            auditable: $targetUser,
            newValues: [
                'impersonated_user_id' => $targetUser->id,
                'impersonated_user_email' => $targetUser->email,
            ],
            userId: $impersonator->id
        );

        // Log in as the impersonated user
        Auth::login($targetUser);
        $request->session()->regenerate();

        return [
            'success' => true,
            'user' => $targetUser,
        ];
    }

    /**
     * Stop impersonating and return to original user.
     *
     * @param  Request  $request  The current request
     * @return array{success: bool, impersonator?: User, error?: string, logout?: bool}
     */
    public function stopImpersonation(Request $request): array
    {
        $impersonatorId = $request->session()->pull('impersonator_id');

        if (!$impersonatorId) {
            return [
                'success' => false,
                'error' => 'No active impersonation session.',
            ];
        }

        $impersonatedUserId = Auth::id();
        $impersonator = $this->userService->findById($impersonatorId);

        if (!$impersonator) {
            // Original impersonator no longer exists, logout completely
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return [
                'success' => false,
                'error' => 'The original user account no longer exists.',
                'logout' => true,
            ];
        }

        // Log the impersonation end (only if current user still exists)
        if (Auth::check()) {
            AuditLog::log(
                event: AuditEvent::IMPERSONATION_STOPPED,
                newValues: [
                    'impersonated_user_id' => $impersonatedUserId,
                    'returned_to_user_id' => $impersonator->id,
                ],
                userId: $impersonatorId
            );
        }

        // Log back in as original user
        Auth::login($impersonator);
        $request->session()->regenerate();

        return [
            'success' => true,
            'impersonator' => $impersonator,
        ];
    }

    /**
     * Check if currently impersonating.
     *
     * @param  Request  $request  The current request
     * @return bool True if currently impersonating
     */
    public function isImpersonating(Request $request): bool
    {
        return $request->session()->has('impersonator_id');
    }

    /**
     * Get the original impersonator user.
     *
     * @param  Request  $request  The current request
     * @return User|null The impersonator or null if not impersonating
     */
    public function getImpersonator(Request $request): ?User
    {
        $impersonatorId = $request->session()->get('impersonator_id');

        if (!$impersonatorId) {
            return null;
        }

        return $this->userService->findById($impersonatorId);
    }

    /**
     * Get users available for impersonation.
     *
     * Excludes the current user from the list.
     *
     * @param  int  $currentUserId  The ID of the current user
     * @param  int  $limit  Maximum number of users to return
     * @return \Illuminate\Database\Eloquent\Collection<int, User>
     */
    public function getUsersForImpersonation(int $currentUserId, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return $this->userService->getAllForImpersonation($currentUserId);
    }

    /**
     * Search users for impersonation.
     *
     * @param  string  $search  The search term
     * @param  int  $limit  Maximum number of users to return
     * @return \Illuminate\Database\Eloquent\Collection<int, User>
     */
    public function searchUsers(string $search, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return $this->userService->search($search, $limit);
    }
}
