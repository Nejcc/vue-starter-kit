<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\FailedJobRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

final class FailedJobRepository extends AbstractNonModelRepository implements FailedJobRepositoryInterface
{
    public function getPaginated(?string $search, ?string $queue, int $perPage = 20): LengthAwarePaginator
    {
        $query = DB::table('failed_jobs')->orderByDesc('failed_at');

        if ($search) {
            $query->where(function ($q) use ($search): void {
                $q->where('queue', 'like', "%{$search}%")
                    ->orWhere('payload', 'like', "%{$search}%")
                    ->orWhere('exception', 'like', "%{$search}%");
            });
        }

        if ($queue) {
            $query->where('queue', $queue);
        }

        return $query->paginate($perPage);
    }

    public function findById(int $id): ?object
    {
        return DB::table('failed_jobs')->where('id', $id)->first();
    }

    /** @return array<int, string> */
    public function getDistinctQueues(): array
    {
        return DB::table('failed_jobs')
            ->select('queue')
            ->distinct()
            ->pluck('queue')
            ->toArray();
    }

    /** @return array{total: int, queues: int} */
    public function getStats(): array
    {
        $queues = $this->getDistinctQueues();

        return [
            'total' => DB::table('failed_jobs')->count(),
            'queues' => count($queues),
        ];
    }

    public function deleteById(int $id): void
    {
        DB::table('failed_jobs')->where('id', $id)->delete();
    }

    public function retryByUuid(string $uuid): void
    {
        Artisan::call('queue:retry', ['id' => [$uuid]]);
    }

    public function retryAll(): void
    {
        Artisan::call('queue:retry', ['id' => ['all']]);
    }

    public function flushAll(): void
    {
        Artisan::call('queue:flush');
    }

    /** @param array<string, mixed>|null $payload */
    public function extractJobName(?array $payload): string
    {
        if (!$payload) {
            return 'Unknown';
        }

        $displayName = $payload['displayName'] ?? null;

        if ($displayName) {
            return class_basename($displayName);
        }

        $data = json_decode($payload['data']['command'] ?? '{}', true);

        return class_basename($data['commandName'] ?? 'Unknown');
    }

    public function extractExceptionSummary(string $exception): string
    {
        $firstLine = strtok($exception, "\n");

        if ($firstLine === false) {
            return 'Unknown error';
        }

        return mb_strlen($firstLine) > 150
            ? mb_substr($firstLine, 0, 150) . '...'
            : $firstLine;
    }
}
