<?php

namespace App\Actions\User;

use App\Contracts\Actions\ActionInterface;
use App\Contracts\Services\UserServiceInterface;

class DeleteUserAction implements ActionInterface
{
    /**
     * Create a new delete user action instance.
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
