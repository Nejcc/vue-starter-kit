<?php

namespace App\Repositories;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Models\User;

/**
 * User repository implementation.
 *
 * Provides data access methods for User models with caching support.
 * Extends AbstractRepository to inherit base CRUD operations.
 */
class UserRepository extends AbstractRepository implements UserRepositoryInterface
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
}
