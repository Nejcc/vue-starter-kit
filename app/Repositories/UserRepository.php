<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Models\User;

/**
 * User repository implementation.
 *
 * Provides data access methods for User models.
 * Extends BaseRepository to inherit base CRUD operations.
 */
final class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    /**
     * Create a new user repository instance.
     */
    public function __construct()
    {
        parent::__construct(User::class);
    }

    /**
     * Find a user by email address.
     *
     * @param  string  $email  The email address to search for
     * @param  array<string>  $columns  The columns to retrieve
     * @return User|null The user instance or null if not found
     *
     * @example
     * $user = $repository->findByEmail('user@example.com');
     */
    public function findByEmail(string $email, array $columns = ['*']): ?User
    {
        return $this->query()
            ->where('email', $email)
            ->first($columns);
    }

    /**
     * Find a user by ID.
     *
     * @param  int  $id  The user ID
     * @param  array<string>  $columns  The columns to retrieve
     * @return User|null The user instance or null if not found
     *
     * @example
     * $user = $repository->findById(1);
     */
    public function findById(int $id, array $columns = ['*']): ?User
    {
        return $this->find($id, $columns);
    }

    /**
     * Create a new user.
     *
     * @param  array<string, mixed>  $attributes  The user attributes
     * @return User The newly created user instance
     *
     * @example
     * $user = $repository->createUser([
     *     'name' => 'John Doe',
     *     'email' => 'john@example.com',
     *     'password' => 'hashed_password',
     * ]);
     */
    public function createUser(array $attributes): User
    {
        return $this->create($attributes);
    }

    /**
     * Update user information.
     *
     * @param  int  $id  The user ID
     * @param  array<string, mixed>  $attributes  The attributes to update
     * @return bool True if the update was successful, false otherwise
     *
     * @example
     * $success = $repository->updateUser(1, ['name' => 'Updated Name']);
     */
    public function updateUser(int $id, array $attributes): bool
    {
        return $this->update($id, $attributes);
    }

    /**
     * Delete a user.
     *
     * @param  int  $id  The user ID
     * @return bool True if the deletion was successful, false otherwise
     *
     * @example
     * $success = $repository->deleteUser(1);
     */
    public function deleteUser(int $id): bool
    {
        return $this->delete($id);
    }

    /**
     * Update user password.
     *
     * @param  int  $id  The user ID
     * @param  string  $password  The hashed password
     * @return bool True if the update was successful, false otherwise
     *
     * @example
     * $success = $repository->updatePassword(1, Hash::make('new_password'));
     */
    public function updatePassword(int $id, string $password): bool
    {
        return $this->update($id, [
            'password' => $password,
        ]);
    }

    /**
     * Search users by name or email.
     *
     * @param  string  $search  The search query
     * @param  int  $limit  Maximum number of results
     * @param  array<string>  $columns  The columns to retrieve
     * @return \Illuminate\Database\Eloquent\Collection<int, User> Collection of matching users
     *
     * @example
     * $users = $repository->search('john');
     */
    public function search(string $search, int $limit = 50, array $columns = ['*']): \Illuminate\Database\Eloquent\Collection
    {
        return $this->query()
            ->where(function ($query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->limit($limit)
            ->get($columns);
    }

    /**
     * Get all users for impersonation (excluding current user).
     *
     * @param  int  $excludeUserId  The user ID to exclude
     * @param  array<string>  $columns  The columns to retrieve
     * @return \Illuminate\Database\Eloquent\Collection<int, User> Collection of users
     *
     * @example
     * $users = $repository->getAllForImpersonation(1);
     */
    public function getAllForImpersonation(int $excludeUserId, array $columns = ['*']): \Illuminate\Database\Eloquent\Collection
    {
        return $this->query()
            ->where('id', '!=', $excludeUserId)
            ->orderBy('name')
            ->get($columns);
    }
}
