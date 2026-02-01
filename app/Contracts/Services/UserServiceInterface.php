<?php

declare(strict_types=1);

namespace App\Contracts\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

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

    /**
     * Get paginated users with roles for admin listing.
     */
    public function getAdminPaginated(?string $search, int $perPage = 15): LengthAwarePaginator;

    /**
     * Create a user from admin panel with role assignment.
     *
     * @param  array<string, mixed>  $data
     */
    public function adminCreate(array $data): User;

    /**
     * Update a user from admin panel with role sync.
     *
     * @param  array<string, mixed>  $data
     */
    public function adminUpdate(int $userId, array $data): User;

    /**
     * Delete a user from admin panel.
     */
    public function adminDelete(int $userId): bool;

    /**
     * Get total user count.
     */
    public function getTotalCount(): int;

    /**
     * Get verified user count.
     */
    public function getVerifiedCount(): int;

    /**
     * Get recent users.
     *
     * @return Collection<int, User>
     */
    public function getRecentUsers(int $limit = 5): Collection;
}
