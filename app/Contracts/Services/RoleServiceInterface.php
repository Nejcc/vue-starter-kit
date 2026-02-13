<?php

declare(strict_types=1);

namespace App\Contracts\Services;

use App\Models\Role;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use InvalidArgumentException;

interface RoleServiceInterface
{
    /**
     * Get all roles with permissions and optional search.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function getAll(?string $search = null): Collection;

    /**
     * Get paginated roles with permissions and optional search.
     */
    public function getPaginated(?string $search = null, int $perPage = 15): LengthAwarePaginator;

    /**
     * Create a new role with optional permissions.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Role;

    /**
     * Update a role with permissions.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Role $role, array $data): Role;

    /**
     * Delete a role.
     */
    public function delete(Role $role): bool;

    /**
     * Get formatted role data for editing.
     *
     * @return array<string, mixed>
     */
    public function getForEdit(Role $role): array;

    /**
     * Get all permission names.
     */
    public function getAllPermissions(): Collection;

    /**
     * Get all role names.
     */
    public function getAllRoleNames(): Collection;

    /**
     * Get total number of roles.
     */
    public function getTotalCount(): int;

    /**
     * Get role permissions data for the dedicated permissions page.
     *
     * @return array<string, mixed>
     */
    public function getPermissionsData(Role $role): array;

    /**
     * Sync permissions on a role.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws InvalidArgumentException If trying to modify super-admin permissions
     */
    public function syncPermissions(Role $role, array $data): Role;
}
