<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Contracts\RepositoryInterface;
use App\Models\Role;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * @extends RepositoryInterface<Role>
 */
interface RoleRepositoryInterface extends RepositoryInterface
{
    public function findById(int $id): ?Role;

    public function getAllWithPermissions(?string $search = null): Collection;

    public function paginateWithPermissions(?string $search = null, int $perPage = 15): LengthAwarePaginator;

    public function getAllPermissionNames(): Collection;
}
