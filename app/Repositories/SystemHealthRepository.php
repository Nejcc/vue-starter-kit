<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\SystemHealthRepositoryInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

final class SystemHealthRepository extends AbstractNonModelRepository implements SystemHealthRepositoryInterface
{
    /** @return array<string, mixed> */
    public function checkDatabaseConnection(): array
    {
        try {
            $start = microtime(true);
            DB::select('SELECT 1');
            $time = round((microtime(true) - $start) * 1000, 2);

            $driver = DB::connection()->getDriverName();
            $tableCount = match ($driver) {
                'sqlite' => count(DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'")),
                'mysql', 'mariadb' => count(DB::select('SHOW TABLES')),
                'pgsql' => count(DB::select("SELECT tablename FROM pg_tables WHERE schemaname = 'public'")),
                default => 0,
            };

            return $this->buildCheckResult('Database', 'ok', "Connected ({$time}ms, {$tableCount} tables)", [
                'driver' => config('database.default'),
                'response_time' => "{$time}ms",
                'tables' => $tableCount,
            ]);
        } catch (Throwable $e) {
            return $this->buildCheckResult('Database', 'error', 'Connection failed: ' . $e->getMessage());
        }
    }

    /** @return array<string, mixed> */
    public function checkCacheConnection(): array
    {
        try {
            $key = 'health_check_' . uniqid();
            $start = microtime(true);
            Cache::put($key, 'ok', 10);
            $value = Cache::get($key);
            Cache::forget($key);
            $time = round((microtime(true) - $start) * 1000, 2);

            if ($value !== 'ok') {
                return $this->buildCheckResult('Cache', 'warning', 'Cache write/read mismatch', [
                    'driver' => config('cache.default'),
                ]);
            }

            return $this->buildCheckResult('Cache', 'ok', "Working ({$time}ms)", [
                'driver' => config('cache.default'),
                'response_time' => "{$time}ms",
            ]);
        } catch (Throwable $e) {
            return $this->buildCheckResult('Cache', 'error', 'Failed: ' . $e->getMessage(), [
                'driver' => config('cache.default'),
            ]);
        }
    }

    /** @return array<string, mixed> */
    public function checkStorageStatus(): array
    {
        try {
            $storagePath = storage_path();
            $isWritable = is_writable($storagePath);
            $freeSpace = disk_free_space($storagePath);
            $totalSpace = disk_total_space($storagePath);
            $usedPercent = round(($totalSpace - $freeSpace) / $totalSpace * 100, 1);

            $status = $usedPercent > 90 ? 'warning' : 'ok';
            $freeFormatted = $this->formatBytes($freeSpace);

            return $this->buildCheckResult(
                'Storage',
                $isWritable ? $status : 'error',
                $isWritable
                    ? "{$freeFormatted} free ({$usedPercent}% used)"
                    : 'Storage directory is not writable',
                [
                    'writable' => $isWritable,
                    'free_space' => $freeFormatted,
                    'total_space' => $this->formatBytes($totalSpace),
                    'used_percent' => "{$usedPercent}%",
                ],
            );
        } catch (Throwable $e) {
            return $this->buildCheckResult('Storage', 'error', 'Check failed: ' . $e->getMessage());
        }
    }

    /** @return array<string, mixed> */
    public function checkQueueStatus(): array
    {
        $driver = config('queue.default');

        if ($driver === 'sync') {
            return $this->buildCheckResult('Queue', 'warning', 'Using sync driver (no background processing)', [
                'driver' => $driver,
            ]);
        }

        try {
            $failedCount = DB::table('failed_jobs')->count();

            return $this->buildCheckResult(
                'Queue',
                $failedCount > 0 ? 'warning' : 'ok',
                $failedCount > 0
                    ? "{$failedCount} failed job(s)"
                    : "Connected ({$driver} driver)",
                [
                    'driver' => $driver,
                    'failed_jobs' => $failedCount,
                ],
            );
        } catch (Throwable) {
            return $this->buildCheckResult('Queue', 'ok', "Using {$driver} driver", [
                'driver' => $driver,
            ]);
        }
    }

    /** @return array<string, mixed> */
    public function checkSchedulerStatus(): array
    {
        $schedulerLock = storage_path('framework/schedule-*');
        $lockFiles = glob($schedulerLock);

        if (empty($lockFiles)) {
            return $this->buildCheckResult('Scheduler', 'warning', 'No scheduler lock found (may not be running)');
        }

        return $this->buildCheckResult('Scheduler', 'ok', 'Scheduler appears active');
    }

    /**
     * @param  array<string, mixed>  $details
     * @return array{name: string, status: string, message: string, details: array<string, mixed>}
     */
    private function buildCheckResult(string $name, string $status, string $message, array $details = []): array
    {
        return [
            'name' => $name,
            'status' => $status,
            'message' => $message,
            'details' => $details,
        ];
    }

    private function formatBytes(float $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 1) . ' ' . $units[$i];
    }
}
