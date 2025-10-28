<?php

namespace App\Actions\User;

use App\Contracts\Actions\ActionInterface;
use App\Contracts\Services\UserServiceInterface;
use App\Models\User;

class UpdateUserProfileAction implements ActionInterface
{
    /**
     * Create a new update user profile action instance.
     */
    public function __construct(
        private readonly UserServiceInterface $userService
    ) {}

    /**
     * Execute the action.
     */
    public function handle(mixed ...$parameters): User
    {
        $userId = $parameters[0] ?? 0;
        $data = $parameters[1] ?? [];

        return $this->userService->updateProfile($userId, $data);
    }
}
