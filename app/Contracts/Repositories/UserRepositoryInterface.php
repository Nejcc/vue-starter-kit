<?php

declare(strict_types=1);

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

    /**
     * Search users by name or email.
     *
     * @param  string  $search  The search query
     * @param  int  $limit  Maximum number of results
     * @param  array<string>  $columns  The columns to retrieve
     * @return \Illuminate\Database\Eloquent\Collection<int, User> Collection of matching users
     */
    public function search(string $search, int $limit = 50, array $columns = ['*']): \Illuminate\Database\Eloquent\Collection;

    /**
     * Get all users for impersonation (excluding current user).
     *
     * @param  int  $excludeUserId  The user ID to exclude
     * @param  array<string>  $columns  The columns to retrieve
     * @return \Illuminate\Database\Eloquent\Collection<int, User> Collection of users
     */
    public function getAllForImpersonation(int $excludeUserId, array $columns = ['*']): \Illuminate\Database\Eloquent\Collection;
}
