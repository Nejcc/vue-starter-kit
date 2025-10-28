<?php

namespace App\Contracts\Repositories;

use App\Contracts\RepositoryInterface;
use App\Models\User;

/**
 * @extends RepositoryInterface<User>
 */
interface UserRepositoryInterface extends RepositoryInterface
{
    /**
     * Find a user by email address.
     *
     * @param  array<string>  $columns
     */
    public function findByEmail(string $email, array $columns = ['*']): ?User;

    /**
     * Find a user by ID.
     *
     * @param  array<string>  $columns
     */
    public function findById(int $id, array $columns = ['*']): ?User;

    /**
     * Create a new user.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function createUser(array $attributes): User;

    /**
     * Update user information.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function updateUser(int $id, array $attributes): bool;

    /**
     * Delete a user.
     */
    public function deleteUser(int $id): bool;

    /**
     * Update user password.
     */
    public function updatePassword(int $id, string $password): bool;
}
