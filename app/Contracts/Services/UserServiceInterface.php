<?php

declare(strict_types=1);

namespace App\Contracts\Services;

use App\Models\User;

interface UserServiceInterface
{
    /**
     * Create a new user with validation.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): User;

    /**
     * Update user profile information.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateProfile(int $userId, array $data): User;

    /**
     * Update user password.
     */
    public function updatePassword(int $userId, string $currentPassword, string $newPassword): bool;

    /**
     * Delete user account.
     */
    public function delete(int $userId, string $password): bool;

    /**
     * Find user by ID.
     */
    public function findById(int $id): ?User;

    /**
     * Find user by email.
     */
    public function findByEmail(string $email): ?User;
}
