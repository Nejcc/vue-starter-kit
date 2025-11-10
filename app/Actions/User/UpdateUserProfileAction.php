<?php

namespace App\Actions\User;

use App\Contracts\Actions\ActionInterface;
use App\Contracts\Services\UserServiceInterface;
use App\Models\User;

/**
 * Action for updating a user's profile information.
 *
 * Handles profile updates including name and email changes. Automatically
 * resets email verification if the email address changes.
 */
class UpdateUserProfileAction implements ActionInterface
{
    /**
     * Create a new update user profile action instance.
     *
     * @param  UserServiceInterface  $userService  The user service instance
     */
    public function __construct(
        private readonly UserServiceInterface $userService
    ) {}

    /**
     * Execute the action.
     *
     * Updates a user's profile information with validation. If the email
     * address changes, the email verification status is reset.
     *
     * @param  mixed  ...$parameters  Parameters: userId (int), data (array)
     * @return User The updated user instance
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If user is not found
     * @throws \Illuminate\Validation\ValidationException If validation fails
     */
    public function handle(mixed ...$parameters): User
    {
        $userId = $parameters[0] ?? 0;
        $data = $parameters[1] ?? [];

        return $this->userService->updateProfile($userId, $data);
    }
}
