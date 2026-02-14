<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

final class HealthController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/Health/Index', [
            'checks' => $this->runChecks(),
            'system' => $this->getSystemInfo(),
        ]);
    }

    /** @return array<int, array<string, mixed>> */
    private function runChecks(): array
    {
        return [
            $this->checkDatabase(),
            $this->checkCache(),
            $this->checkStorage(),
            $this->checkQueue(),
            $this->checkScheduler(),
        ];
    }

    /** @return array<string, mixed> */
    private function checkDatabase(): array
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

            return [
                'name' => 'Database',
                'status' => 'ok',
                'message' => "Connected ({$time}ms, {$tableCount} tables)",
                'details' => [
                    'driver' => config('database.default'),
                    'response_time' => "{$time}ms",
                    'tables' => $tableCount,
                ],
            ];
        } catch (Throwable $e) {
            return [
                'name' => 'Database',
                'status' => 'error',
                'message' => 'Connection failed: ' . $e->getMessage(),
                'details' => [],
            ];
        }
    }

    /** @return array<string, mixed> */
    private function checkCache(): array
    {
        try {
            $key = 'health_check_' . uniqid();
            $start = microtime(true);
            Cache::put($key, 'ok', 10);
            $value = Cache::get($key);
            Cache::forget($key);
            $time = round((microtime(true) - $start) * 1000, 2);

            if ($value !== 'ok') {
                return [
                    'name' => 'Cache',
                    'status' => 'warning',
                    'message' => 'Cache write/read mismatch',
                    'details' => ['driver' => config('cache.default')],
                ];
            }

            return [
                'name' => 'Cache',
                'status' => 'ok',
                'message' => "Working ({$time}ms)",
                'details' => [
                    'driver' => config('cache.default'),
                    'response_time' => "{$time}ms",
                ],
            ];
        } catch (Throwable $e) {
            return [
                'name' => 'Cache',
                'status' => 'error',
                'message' => 'Failed: ' . $e->getMessage(),
                'details' => ['driver' => config('cache.default')],
            ];
        }
    }

    /** @return array<string, mixed> */
    private function checkStorage(): array
    {
        try {
            $storagePath = storage_path();
            $isWritable = is_writable($storagePath);
            $freeSpace = disk_free_space($storagePath);
            $totalSpace = disk_total_space($storagePath);
            $usedPercent = round(($totalSpace - $freeSpace) / $totalSpace * 100, 1);

            $status = $usedPercent > 90 ? 'warning' : 'ok';
            $freeFormatted = $this->formatBytes($freeSpace);

            return [
                'name' => 'Storage',
                'status' => $isWritable ? $status : 'error',
                'message' => $isWritable
                    ? "{$freeFormatted} free ({$usedPercent}% used)"
                    : 'Storage directory is not writable',
                'details' => [
                    'writable' => $isWritable,
                    'free_space' => $freeFormatted,
                    'total_space' => $this->formatBytes($totalSpace),
                    'used_percent' => "{$usedPercent}%",
                ],
            ];
        } catch (Throwable $e) {
            return [
                'name' => 'Storage',
                'status' => 'error',
                'message' => 'Check failed: ' . $e->getMessage(),
                'details' => [],
            ];
        }
    }

    /** @return array<string, mixed> */
    private function checkQueue(): array
    {
        $driver = config('queue.default');

        if ($driver === 'sync') {
            return [
                'name' => 'Queue',
                'status' => 'warning',
                'message' => 'Using sync driver (no background processing)',
                'details' => ['driver' => $driver],
            ];
        }

        try {
            $failedCount = DB::table('failed_jobs')->count();

            return [
                'name' => 'Queue',
                'status' => $failedCount > 0 ? 'warning' : 'ok',
                'message' => $failedCount > 0
                    ? "{$failedCount} failed job(s)"
                    : "Connected ({$driver} driver)",
                'details' => [
                    'driver' => $driver,
                    'failed_jobs' => $failedCount,
                ],
            ];
        } catch (Throwable) {
            return [
                'name' => 'Queue',
                'status' => 'ok',
                'message' => "Using {$driver} driver",
                'details' => ['driver' => $driver],
            ];
        }
    }

    /** @return array<string, mixed> */
    private function checkScheduler(): array
    {
        $schedulerLock = storage_path('framework/schedule-*');
        $lockFiles = glob($schedulerLock);

        if (empty($lockFiles)) {
            return [
                'name' => 'Scheduler',
                'status' => 'warning',
                'message' => 'No scheduler lock found (may not be running)',
                'details' => [],
            ];
        }

        return [
            'name' => 'Scheduler',
            'status' => 'ok',
            'message' => 'Scheduler appears active',
            'details' => [],
        ];
    }

    /** @return array<string, mixed> */
    private function getSystemInfo(): array
    {
        return [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'environment' => app()->environment(),
            'debug_mode' => config('app.debug'),
            'timezone' => config('app.timezone'),
            'locale' => config('app.locale'),
            'server_time' => now()->toDateTimeString(),
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
