<?php

namespace App\Actions\User;

use App\Contracts\Actions\ActionInterface;
use App\Contracts\Services\UserServiceInterface;
use App\Models\User;

class CreateUserAction implements ActionInterface
{
    /**
     * Create a new create user action instance.
     */
    public function __construct(
        private readonly UserServiceInterface $userService
    ) {}

    /**
     * Execute the action.
     */
    public function handle(mixed ...$parameters): User
    {
        $data = $parameters[0] ?? [];

        return $this->userService->create($data);
    }
}
