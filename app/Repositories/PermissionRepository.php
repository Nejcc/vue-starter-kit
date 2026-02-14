<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\PermissionRepositoryInterface;
use App\Models\Permission;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
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
        return $this->buildPermissionsQuery($search)->get();
    }

    public function paginateWithRoles(?string $search = null, int $perPage = 15, ?string $group = null): LengthAwarePaginator
    {
        return $this->buildPermissionsQuery($search, $group)->paginate($perPage);
    }

    /**
     * Get all distinct group names.
     *
     * @return Collection<int, string>
     */
    public function getGroupNames(): Collection
    {
        return $this->query()
            ->whereNotNull('group_name')
            ->distinct()
            ->orderBy('group_name')
            ->pluck('group_name');
    }

    private function buildPermissionsQuery(?string $search = null, ?string $group = null): Builder
    {
        $query = $this->query()->with('roles');

        if ($search) {
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('group_name', 'like', "%{$search}%");
            });
        }

        if ($group !== null) {
            if ($group === '') {
                $query->whereNull('group_name');
            } else {
                $query->where('group_name', $group);
            }
        }

        return $query->latest();
    }
}
