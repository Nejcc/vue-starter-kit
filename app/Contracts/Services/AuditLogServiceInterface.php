<?php

declare(strict_types=1);

namespace App\Contracts\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection as SupportCollection;

interface AuditLogServiceInterface
{
    public function getFilteredPaginated(?string $search, ?string $event, int $perPage = 25): LengthAwarePaginator;

    public function getDistinctEventTypes(): SupportCollection;

    public function getRecentWithUser(int $limit = 10): Collection;
}
