<?php

namespace App\Repositories;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Models\User;

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
     * @param  array<string>  $columns
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
     * @param  array<string>  $columns
     */
    public function findById(int $id, array $columns = ['*']): ?User
    {
        return $this->find($id, $columns);
    }

    /**
     * Create a new user.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function createUser(array $attributes): User
    {
        return $this->create($attributes);
    }

    /**
     * Update user information.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function updateUser(int $id, array $attributes): bool
    {
        return $this->update($id, $attributes);
    }

    /**
     * Delete a user.
     */
    public function deleteUser(int $id): bool
    {
        return $this->delete($id);
    }

    /**
     * Update user password.
     */
    public function updatePassword(int $id, string $password): bool
    {
        return $this->update($id, [
            'password' => $password,
        ]);
    }
}
