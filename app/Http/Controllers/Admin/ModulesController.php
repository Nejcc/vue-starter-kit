<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PackageManagerService;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

final class ModulesController extends Controller
{
    public function __construct(
        private readonly PackageManagerService $packageManager,
    ) {}

    public function index(): Response
    {
        // Get managed packages from the service
        $modules = $this->packageManager->getAll();

        // Add Horizon (not a managed/toggleable package)
        $modules[] = [
            'key' => 'horizon',
            'name' => 'Horizon',
            'description' => 'Redis queue monitoring dashboard with metrics and job management.',
            'icon' => 'Activity',
            'package' => 'laravel/horizon',
            'enabled' => true,
            'installed' => Route::has('horizon.index'),
            'settingsUrl' => null,
            'adminUrl' => Route::has('horizon.index') ? '/horizon' : null,
            'required' => false,
        ];

        return Inertia::render('admin/Modules/Index', [
            'modules' => $modules,
        ]);
    }
}
