<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Contracts\RepositoryInterface;
use App\Models\AuditLog;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * @extends RepositoryInterface<AuditLog>
 */
interface AuditLogRepositoryInterface extends RepositoryInterface
{
    public function getFilteredPaginated(?string $search, ?string $event, int $perPage = 25): LengthAwarePaginator;

    public function getDistinctEventTypes(): Collection;

    public function getRecentWithUser(int $limit = 10): Collection;
}
