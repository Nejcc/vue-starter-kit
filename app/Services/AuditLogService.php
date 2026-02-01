<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Repositories\AuditLogRepositoryInterface;
use App\Contracts\Services\AuditLogServiceInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection as SupportCollection;

final class AuditLogService extends AbstractService implements AuditLogServiceInterface
{
    public function __construct(AuditLogRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    public function getFilteredPaginated(?string $search, ?string $event, int $perPage = 25): LengthAwarePaginator
    {
        return $this->getRepository()->getFilteredPaginated($search, $event, $perPage);
    }

    public function getDistinctEventTypes(): SupportCollection
    {
        return $this->getRepository()->getDistinctEventTypes();
    }

    public function getRecentWithUser(int $limit = 10): Collection
    {
        return $this->getRepository()->getRecentWithUser($limit);
    }
}
