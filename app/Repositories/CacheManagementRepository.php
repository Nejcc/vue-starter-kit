<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\CacheManagementRepositoryInterface;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Throwable;

final class CacheManagementRepository extends AbstractNonModelRepository implements CacheManagementRepositoryInterface
{
    /** @return array<string, mixed> */
    public function getDriverInfo(): array
    {
        $driver = config('cache.default');
        $stores = array_keys(config('cache.stores', []));
        $prefix = config('cache.prefix', '');

        return [
            'default' => $driver,
            'stores' => $stores,
            'prefix' => $prefix,
        ];
    }

    /** @return array<string, mixed> */
    public function getCacheStats(): array
    {
        $driver = config('cache.default');
        $itemCount = 0;
        $expiredCount = 0;

        if ($driver === 'database') {
            try {
                $table = config('cache.stores.database.table', 'cache');
                $itemCount = DB::table($table)->count();
                $expiredCount = DB::table($table)
                    ->where('expiration', '<', time())
                    ->count();
            } catch (Throwable) {
                // Database table may not exist
            }
        }

        return [
            'items' => $itemCount,
            'expired' => $expiredCount,
            'active' => $itemCount - $expiredCount,
        ];
    }

    /** @return array<int, array<string, mixed>> */
    public function getCacheItems(): array
    {
        $driver = config('cache.default');
        $items = [];

        if ($driver === 'database') {
            try {
                $table = config('cache.stores.database.table', 'cache');
                $prefix = config('cache.prefix', '');
                $rows = DB::table($table)
                    ->orderByDesc('expiration')
                    ->limit(50)
                    ->get();

                foreach ($rows as $row) {
                    $key = $row->key;
                    if ($prefix && str_starts_with($key, $prefix)) {
                        $key = mb_substr($key, mb_strlen($prefix));
                    }

                    $isExpired = $row->expiration < time();
                    $expiresAt = $row->expiration > 0
                        ? date('Y-m-d H:i:s', $row->expiration)
                        : 'Never';

                    $items[] = [
                        'key' => $key,
                        'full_key' => $row->key,
                        'size' => mb_strlen($row->value),
                        'expires_at' => $expiresAt,
                        'is_expired' => $isExpired,
                    ];
                }
            } catch (Throwable) {
                // Database table may not exist
            }
        }

        return $items;
    }

    /** @return array<string, mixed> */
    public function getMaintenanceStatus(): array
    {
        return [
            'is_down' => app()->isDownForMaintenance(),
        ];
    }

    public function clearArtisanCache(string $command): void
    {
        Artisan::call($command);
    }

    /**
     * @return array<int, string> List of error messages for failed commands
     */
    public function clearAllCaches(): array
    {
        $errors = [];

        $commands = [
            'cache:clear' => 'application cache',
            'view:clear' => 'compiled views',
            'route:clear' => 'route cache',
            'config:clear' => 'config cache',
            'event:clear' => 'event cache',
        ];

        foreach ($commands as $command => $label) {
            try {
                Artisan::call($command);
            } catch (Throwable $e) {
                $errors[] = "Failed to clear {$label}: " . $e->getMessage();
            }
        }

        return $errors;
    }

    /** @param array<string, string> $params */
    public function enableMaintenance(array $params = []): void
    {
        Artisan::call('down', $params);
    }

    public function disableMaintenance(): void
    {
        Artisan::call('up');
    }
}
