<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\RoleRepositoryInterface;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Collection;

/**
 * Role repository implementation.
 *
 * Provides data access methods for Role models.
 */
final class RoleRepository extends BaseRepository implements RoleRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(Role::class);
    }

    public function findById(int $id): ?Role
    {
        return $this->find($id);
    }

    public function getAllWithPermissions(?string $search = null): Collection
    {
        $query = $this->query()->with('permissions');

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        return $query->latest()->get();
    }

    public function getAllPermissionNames(): Collection
    {
        return Permission::query()->pluck('name');
    }
}
