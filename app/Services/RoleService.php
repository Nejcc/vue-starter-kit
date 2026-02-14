<?php

declare(strict_types=1);

namespace App\Services;

use App\Constants\AuditEvent;
use App\Constants\RoleNames;
use App\Contracts\Repositories\RoleRepositoryInterface;
use App\Contracts\Services\RoleServiceInterface;
use App\Exceptions\RoleException;
use App\Models\AuditLog;
use App\Models\Role;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Role service implementation.
 *
 * Provides business logic for role management including creation, updates,
 * and deletion with super-admin protection.
 */
final class RoleService extends AbstractService implements RoleServiceInterface
{
    public function __construct(RoleRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    /**
     * Get all roles with permissions and optional search.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function getAll(?string $search = null): Collection
    {
        return $this->getRepository()->getAllWithPermissions($search)->map(fn ($role) => [
            'id' => $role->id,
            'name' => $role->name,
            'is_super_admin' => $role->name === RoleNames::SUPER_ADMIN,
            'permissions' => $role->permissions->pluck('name'),
            'users_count' => $role->users_count ?? $role->users()->count(),
            'created_at' => $role->created_at,
        ]);
    }

    /**
     * Get paginated roles with permissions and optional search.
     */
    public function getPaginated(?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        return $this->getRepository()->paginateWithPermissions($search, $perPage)->through(fn ($role) => [
            'id' => $role->id,
            'name' => $role->name,
            'is_super_admin' => $role->name === RoleNames::SUPER_ADMIN,
            'permissions' => $role->permissions->pluck('name'),
            'users_count' => $role->users_count ?? $role->users()->count(),
            'created_at' => $role->created_at,
        ]);
    }

    /**
     * Create a new role with optional permissions.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws RoleException If trying to create a super-admin role
     */
    public function create(array $data): Role
    {
        if ($data['name'] === RoleNames::SUPER_ADMIN) {
            throw RoleException::cannotCreateSuperAdmin();
        }

        return $this->transaction(function () use ($data): Role {
            $role = $this->getRepository()->create(['name' => $data['name']]);

            if (!empty($data['permissions']) && is_array($data['permissions'])) {
                $role->givePermissionTo($data['permissions']);
            }

            AuditLog::log(AuditEvent::ROLE_CREATED, $role, null, [
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name')->toArray(),
            ]);

            return $role;
        });
    }

    /**
     * Update a role with permissions.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws RoleException If renaming to/from super-admin
     */
    public function update(Role $role, array $data): Role
    {
        if ($role->name === RoleNames::SUPER_ADMIN && $data['name'] !== RoleNames::SUPER_ADMIN) {
            throw RoleException::cannotRenameSuperAdmin();
        }

        if ($role->name !== RoleNames::SUPER_ADMIN && $data['name'] === RoleNames::SUPER_ADMIN) {
            throw RoleException::cannotUseSuperAdminName();
        }

        $oldValues = [
            'name' => $role->name,
            'permissions' => $role->permissions->pluck('name')->toArray(),
        ];

        return $this->transaction(function () use ($role, $data, $oldValues): Role {
            $this->getRepository()->update($role->id, ['name' => $data['name']]);

            if (isset($data['permissions']) && is_array($data['permissions'])) {
                $role->syncPermissions($data['permissions']);
            } else {
                $role->syncPermissions([]);
            }

            $role->refresh();

            AuditLog::log(AuditEvent::ROLE_UPDATED, $role, $oldValues, [
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name')->toArray(),
            ]);

            return $role;
        });
    }

    /**
     * Delete a role.
     *
     * @throws RoleException If trying to delete super-admin or role with assigned users
     */
    public function delete(Role $role): bool
    {
        if ($role->name === RoleNames::SUPER_ADMIN) {
            throw RoleException::cannotDeleteSuperAdmin();
        }

        $usersCount = $role->users()->count();
        if ($usersCount > 0) {
            throw RoleException::cannotDeleteWithUsers($usersCount);
        }

        AuditLog::log(AuditEvent::ROLE_DELETED, $role, [
            'name' => $role->name,
        ]);

        return $this->getRepository()->delete($role->id);
    }

    /**
     * Get formatted role data for editing.
     *
     * @return array<string, mixed>
     */
    public function getForEdit(Role $role): array
    {
        return [
            'id' => $role->id,
            'name' => $role->name,
            'is_super_admin' => $role->name === RoleNames::SUPER_ADMIN,
            'permissions' => $role->permissions->pluck('name'),
        ];
    }

    /**
     * Get all available permissions.
     *
     * @return Collection<int, string>
     */
    public function getAllPermissions(): Collection
    {
        return $this->getRepository()->getAllPermissionNames();
    }

    /**
     * Get all role names.
     *
     * @return Collection<int, string>
     */
    public function getAllRoleNames(): Collection
    {
        return $this->getRepository()->all()->pluck('name');
    }

    /**
     * Get total number of roles.
     */
    public function getTotalCount(): int
    {
        return $this->getRepository()->all()->count();
    }

    /**
     * Get role permissions data for the dedicated permissions page.
     *
     * @return array<string, mixed>
     */
    public function getPermissionsData(Role $role): array
    {
        return [
            'id' => $role->id,
            'name' => $role->name,
            'is_super_admin' => $role->name === RoleNames::SUPER_ADMIN,
            'permissions' => $role->permissions->pluck('name')->toArray(),
            'users_count' => $role->users_count ?? $role->users()->count(),
        ];
    }

    /**
     * Sync permissions on a role.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws RoleException If trying to modify super-admin permissions
     */
    public function syncPermissions(Role $role, array $data): Role
    {
        if ($role->name === RoleNames::SUPER_ADMIN) {
            throw RoleException::cannotModifySuperAdminPermissions();
        }

        $oldPermissions = $role->permissions->pluck('name')->toArray();

        return $this->transaction(function () use ($role, $data, $oldPermissions): Role {
            $role->syncPermissions($data['permissions'] ?? []);
            $role->refresh();

            AuditLog::log(AuditEvent::ROLE_PERMISSIONS_SYNCED, $role, [
                'permissions' => $oldPermissions,
            ], [
                'permissions' => $role->permissions->pluck('name')->toArray(),
            ]);

            return $role;
        });
    }
}
