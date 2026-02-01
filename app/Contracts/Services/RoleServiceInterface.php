<?php

declare(strict_types=1);

namespace App\Contracts\Services;

use App\Models\Role;
use Illuminate\Support\Collection;

interface RoleServiceInterface
{
    /**
     * Get all roles with permissions and optional search.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function getAll(?string $search = null): Collection;

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
}
