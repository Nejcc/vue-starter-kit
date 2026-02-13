<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Contracts\RepositoryInterface;
use App\Models\Permission;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * @extends RepositoryInterface<Permission>
 */
interface PermissionRepositoryInterface extends RepositoryInterface
{
    public function findById(int $id): ?Permission;

    public function getAllWithRoles(?string $search = null): Collection;

    public function paginateWithRoles(?string $search = null, int $perPage = 15): LengthAwarePaginator;
}
