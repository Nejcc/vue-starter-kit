<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Services\PermissionServiceInterface;
use App\Models\AuditLog;
use App\Models\Permission;
use Illuminate\Support\Collection;

/**
 * Permission service implementation.
 *
 * Provides business logic for permission management including creation,
 * updates, search, and grouping.
 */
final class PermissionService implements PermissionServiceInterface
{
    /**
     * Get all permissions with roles and optional search.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function getAll(?string $search = null): Collection
    {
        $query = Permission::with('roles');

        if ($search) {
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('group_name', 'like', "%{$search}%");
            });
        }

        return $query->latest()->get()->map(fn ($permission) => [
            'id' => $permission->id,
            'name' => $permission->name,
            'group_name' => $permission->group_name,
            'roles' => $permission->roles->pluck('name'),
            'roles_count' => $permission->roles()->count(),
            'created_at' => $permission->created_at,
        ]);
    }

    /**
     * Get permissions grouped by group_name.
     *
     * @return Collection<string, Collection<int, array<string, mixed>>>
     */
    public function getGrouped(?string $search = null): Collection
    {
        return $this->getAll($search)
            ->groupBy('group_name')
            ->map(fn ($group) => $group->values());
    }

    /**
     * Create a new permission.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Permission
    {
        $permission = Permission::create([
            'name' => $data['name'],
            'group_name' => $data['group_name'] ?? null,
        ]);

        AuditLog::log('permission.created', $permission, null, [
            'name' => $permission->name,
            'group_name' => $permission->group_name,
        ]);

        return $permission;
    }

    /**
     * Update a permission.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Permission $permission, array $data): Permission
    {
        $oldValues = [
            'name' => $permission->name,
            'group_name' => $permission->group_name,
        ];

        $permission->update([
            'name' => $data['name'],
            'group_name' => $data['group_name'] ?? null,
        ]);

        AuditLog::log('permission.updated', $permission, $oldValues, [
            'name' => $permission->name,
            'group_name' => $permission->group_name,
        ]);

        return $permission;
    }

    /**
     * Get formatted permission data for editing.
     *
     * @return array<string, mixed>
     */
    public function getForEdit(Permission $permission): array
    {
        return [
            'id' => $permission->id,
            'name' => $permission->name,
            'group_name' => $permission->group_name,
        ];
    }
}
