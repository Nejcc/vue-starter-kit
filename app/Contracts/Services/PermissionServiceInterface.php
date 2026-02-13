<?php

declare(strict_types=1);

namespace App\Contracts\Services;

use App\Models\Permission;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface PermissionServiceInterface
{
    /**
     * Get all permissions with roles and optional search.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function getAll(?string $search = null): Collection;

    /**
     * Get paginated permissions with roles and optional search.
     */
    public function getPaginated(?string $search = null, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get permissions grouped by group_name.
     *
     * @return Collection<string, Collection<int, array<string, mixed>>>
     */
    public function getGrouped(?string $search = null): Collection;

    /**
     * Create a new permission.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Permission;

    /**
     * Update a permission.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Permission $permission, array $data): Permission;

    /**
     * Get formatted permission data for editing.
     *
     * @return array<string, mixed>
     */
    public function getForEdit(Permission $permission): array;

    /**
     * Get total number of permissions.
     */
    public function getTotalCount(): int;
}
