<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\AuditLogRepositoryInterface;
use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection as SupportCollection;

/**
 * AuditLog repository implementation.
 *
 * Provides data access methods for AuditLog models.
 */
final class AuditLogRepository extends BaseRepository implements AuditLogRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(AuditLog::class);
    }

    public function getFilteredPaginated(?string $search, ?string $event, int $perPage = 25): LengthAwarePaginator
    {
        $query = $this->query()->with('user:id,name,email');

        if ($search) {
            $query->where(function ($q) use ($search): void {
                $q->where('event', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search): void {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($event) {
            $query->where('event', $event);
        }

        return $query->latest()->paginate($perPage)->withQueryString();
    }

    public function getDistinctEventTypes(): SupportCollection
    {
        return $this->query()
            ->select('event')
            ->distinct()
            ->orderBy('event')
            ->pluck('event');
    }

    public function getRecentWithUser(int $limit = 10): Collection
    {
        return $this->query()
            ->with('user:id,name,email')
            ->latest()
            ->limit($limit)
            ->get(['id', 'user_id', 'event', 'auditable_type', 'auditable_id', 'created_at']);
    }

    public function getUserActivityPaginated(int $userId, int $perPage = 20): LengthAwarePaginator
    {
        return $this->query()
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }
}
