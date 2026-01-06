<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Contracts\Actions\ActionInterface;
use App\Contracts\Services\UserServiceInterface;

/**
 * Action for updating a user's password.
 *
 * Handles password updates with current password verification and
 * new password validation.
 */
final class UpdateUserPasswordAction implements ActionInterface
{
    /**
     * Create a new update user password action instance.
     *
     * @param  UserServiceInterface  $userService  The user service instance
     */
    public function __construct(
        private readonly UserServiceInterface $userService
    ) {}

    /**
     * Execute the action.
     *
     * Updates a user's password after verifying the current password
     * and validating the new password strength.
     *
     * @param  mixed  ...$parameters  Parameters: userId (int), currentPassword (string), newPassword (string)
     * @return bool True if the password was updated successfully
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If user is not found
     * @throws \Illuminate\Validation\ValidationException If current password is incorrect or new password is invalid
     */
    public function handle(mixed ...$parameters): bool
    {
        $userId = $parameters[0] ?? 0;
        $currentPassword = $parameters[1] ?? '';
        $newPassword = $parameters[2] ?? '';

        return $this->userService->updatePassword($userId, $currentPassword, $newPassword);
    }
}
