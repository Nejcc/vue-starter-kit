<?php

namespace App\Actions\User;

use App\Contracts\Actions\ActionInterface;
use App\Contracts\Services\UserServiceInterface;

class UpdateUserPasswordAction implements ActionInterface
{
    /**
     * Create a new update user password action instance.
     */
    public function __construct(
        private readonly UserServiceInterface $userService
    ) {}

    /**
     * Execute the action.
     */
    public function handle(mixed ...$parameters): bool
    {
        $userId = $parameters[0] ?? 0;
        $currentPassword = $parameters[1] ?? '';
        $newPassword = $parameters[2] ?? '';

        return $this->userService->updatePassword($userId, $currentPassword, $newPassword);
    }
}
