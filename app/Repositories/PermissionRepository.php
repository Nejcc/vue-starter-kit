<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\PermissionRepositoryInterface;
use App\Models\Permission;
use Illuminate\Support\Collection;

/**
 * Permission repository implementation.
 *
 * Provides data access methods for Permission models.
 */
final class PermissionRepository extends BaseRepository implements PermissionRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(Permission::class);
    }

    public function findById(int $id): ?Permission
    {
        return $this->find($id);
    }

    public function getAllWithRoles(?string $search = null): Collection
    {
        $query = $this->query()->with('roles');

        if ($search) {
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('group_name', 'like', "%{$search}%");
            });
        }

        return $query->latest()->get();
    }
}
