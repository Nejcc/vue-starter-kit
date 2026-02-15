<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Contracts\Services\CacheManagementServiceInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

final class CacheController extends Controller
{
    public function __construct(
        private readonly CacheManagementServiceInterface $cacheService,
    ) {}

    public function index(): Response
    {
        return Inertia::render('admin/Cache/Index', $this->cacheService->getIndexData());
    }

    public function clearCache(): RedirectResponse
    {
        return $this->performClear('cache', 'cache', 'Application cache cleared successfully.');
    }

    public function clearViews(): RedirectResponse
    {
        return $this->performClear('views', 'views', 'Compiled views cleared successfully.');
    }

    public function clearRoutes(): RedirectResponse
    {
        return $this->performClear('routes', 'routes', 'Route cache cleared successfully.');
    }

    public function clearConfig(): RedirectResponse
    {
        return $this->performClear('config', 'config', 'Configuration cache cleared successfully.');
    }

    private function performClear(string $type, string $label, string $successMessage): RedirectResponse
    {
        try {
            $this->cacheService->clearCache($type);
        } catch (Throwable $e) {
            return back()->with('error', "Failed to clear {$label}: " . $e->getMessage());
        }

        return back()->with('success', $successMessage);
    }

    public function clearAll(): RedirectResponse
    {
        try {
            $this->cacheService->clearAllCaches();
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'All caches cleared successfully.');
    }

    public function toggleMaintenance(Request $request): RedirectResponse
    {
        try {
            $result = $this->cacheService->toggleMaintenance($request);

            return back()->with('success', $result['message']);
        } catch (Throwable $e) {
            return back()->with('error', 'Failed to toggle maintenance mode: ' . $e->getMessage());
        }
    }
}
