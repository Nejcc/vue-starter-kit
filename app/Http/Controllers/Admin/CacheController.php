<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Constants\AuditEvent;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

final class CacheController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/Cache/Index', [
            'driver' => $this->getDriverInfo(),
            'stats' => $this->getCacheStats(),
            'items' => $this->getCacheItems(),
            'maintenance' => $this->getMaintenanceStatus(),
        ]);
    }

    public function clearCache(): RedirectResponse
    {
        try {
            Artisan::call('cache:clear');
        } catch (Throwable $e) {
            return back()->with('error', 'Failed to clear cache: ' . $e->getMessage());
        }

        AuditLog::log(AuditEvent::CACHE_CLEARED, null, null, ['type' => 'cache']);

        return back()->with('success', 'Application cache cleared successfully.');
    }

    public function clearViews(): RedirectResponse
    {
        try {
            Artisan::call('view:clear');
        } catch (Throwable $e) {
            return back()->with('error', 'Failed to clear views: ' . $e->getMessage());
        }

        AuditLog::log(AuditEvent::CACHE_CLEARED, null, null, ['type' => 'views']);

        return back()->with('success', 'Compiled views cleared successfully.');
    }

    public function clearRoutes(): RedirectResponse
    {
        try {
            Artisan::call('route:clear');
        } catch (Throwable $e) {
            return back()->with('error', 'Failed to clear routes: ' . $e->getMessage());
        }

        AuditLog::log(AuditEvent::CACHE_CLEARED, null, null, ['type' => 'routes']);

        return back()->with('success', 'Route cache cleared successfully.');
    }

    public function clearConfig(): RedirectResponse
    {
        try {
            Artisan::call('config:clear');
        } catch (Throwable $e) {
            return back()->with('error', 'Failed to clear config: ' . $e->getMessage());
        }

        AuditLog::log(AuditEvent::CACHE_CLEARED, null, null, ['type' => 'config']);

        return back()->with('success', 'Configuration cache cleared successfully.');
    }

    public function clearAll(): RedirectResponse
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

        if ($errors !== []) {
            return back()->with('error', implode('. ', $errors));
        }

        AuditLog::log(AuditEvent::CACHE_CLEARED, null, null, ['type' => 'all']);

        return back()->with('success', 'All caches cleared successfully.');
    }

    public function toggleMaintenance(Request $request): RedirectResponse
    {
        $isDown = app()->isDownForMaintenance();

        try {
            if ($isDown) {
                Artisan::call('up');
                AuditLog::log(AuditEvent::MAINTENANCE_TOGGLED, null, ['maintenance' => true], ['maintenance' => false]);

                return back()->with('success', 'Application is now live.');
            }

            $secret = $request->input('secret');
            $params = [];

            if ($secret) {
                $params['--secret'] = $secret;
            }

            Artisan::call('down', $params);
            AuditLog::log(AuditEvent::MAINTENANCE_TOGGLED, null, ['maintenance' => false], ['maintenance' => true]);

            return back()->with('success', 'Application is now in maintenance mode.' . ($secret ? " Secret bypass: {$secret}" : ''));
        } catch (Throwable $e) {
            return back()->with('error', 'Failed to toggle maintenance mode: ' . $e->getMessage());
        }
    }

    /** @return array<string, mixed> */
    private function getDriverInfo(): array
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
    private function getCacheStats(): array
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
    private function getCacheItems(): array
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
    private function getMaintenanceStatus(): array
    {
        $isDown = app()->isDownForMaintenance();

        return [
            'is_down' => $isDown,
        ];
    }
}
