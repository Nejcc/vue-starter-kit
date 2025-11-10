<?php

namespace App\Actions\User;

use App\Contracts\Actions\ActionInterface;
use App\Contracts\Services\UserServiceInterface;

/**
 * Action for deleting a user account.
 *
 * Handles user account deletion with password verification. Optionally
 * invalidates the session if a request object is provided.
 */
class DeleteUserAction implements ActionInterface
{
    /**
     * Create a new delete user action instance.
     *
     * @param  UserServiceInterface  $userService  The user service instance
     */
    public function __construct(
        private readonly UserServiceInterface $userService
    ) {}

    /**
     * Execute the action.
     *
     * Deletes a user account after verifying the password. If a request
     * object is provided, invalidates and regenerates the session.
     *
     * @param  mixed  ...$parameters  Parameters: userId (int), password (string), request (Request|null)
     * @return bool True if the user was deleted successfully
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If user is not found
     * @throws \Illuminate\Validation\ValidationException If password is incorrect
     */
    public function handle(mixed ...$parameters): bool
    {
        $userId = $parameters[0] ?? 0;
        $password = $parameters[1] ?? '';
        $request = $parameters[2] ?? null;

        $result = $this->userService->delete($userId, $password);

        if ($result && $request) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return $result;
    }
}
