<?php

namespace App\Facades;

use App\Contracts\Services\UserServiceInterface;
use App\Models\User;
use Illuminate\Support\Facades\Facade;

/**
 * @method static User create(array $data)
 * @method static User updateProfile(int $userId, array $data)
 * @method static bool updatePassword(int $userId, string $currentPassword, string $newPassword)
 * @method static bool delete(int $userId, string $password)
 * @method static User|null findById(int $id)
 * @method static User|null findByEmail(string $email)
 *
 * @see \App\Services\UserService
 */
class User extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return UserServiceInterface::class;
    }
}
