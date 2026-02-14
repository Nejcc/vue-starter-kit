<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Contracts\Services\AuditLogServiceInterface;
use App\Contracts\Services\PermissionServiceInterface;
use App\Contracts\Services\RoleServiceInterface;
use App\Contracts\Services\UserServiceInterface;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

final class AdminController extends Controller
{
    public function __construct(
        private readonly UserServiceInterface $userService,
        private readonly RoleServiceInterface $roleService,
        private readonly PermissionServiceInterface $permissionService,
        private readonly AuditLogServiceInterface $auditLogService,
    ) {}

    public function index(): Response
    {
        $stats = [
            'totalUsers' => $this->userService->getTotalCount(),
            'verifiedUsers' => $this->userService->getVerifiedCount(),
            'totalRoles' => $this->roleService->getTotalCount(),
            'totalPermissions' => $this->permissionService->getTotalCount(),
        ];

        $recentUsers = $this->userService->getRecentUsers(5);
        $recentActivity = $this->auditLogService->getRecentWithUser(10);

        return Inertia::render('admin/Dashboard', [
            'stats' => $stats,
            'systemStats' => $this->getSystemStats(),
            'recentUsers' => $recentUsers,
            'recentActivity' => $recentActivity,
        ]);
    }

    /** @return array<string, mixed> */
    private function getSystemStats(): array
    {
        $failedJobs = 0;

        try {
            $failedJobs = DB::table('failed_jobs')->count();
        } catch (Throwable) {
            // Table may not exist
        }

        $cacheDriver = config('cache.default');
        $isMaintenanceMode = app()->isDownForMaintenance();

        $cacheWorking = false;

        try {
            Cache::put('__health_check', true, 5);
            $cacheWorking = Cache::get('__health_check') === true;
            Cache::forget('__health_check');
        } catch (Throwable) {
            // Cache not available
        }

        return [
            'failedJobs' => $failedJobs,
            'cacheDriver' => $cacheDriver,
            'cacheWorking' => $cacheWorking,
            'maintenanceMode' => $isMaintenanceMode,
            'phpVersion' => PHP_VERSION,
            'laravelVersion' => app()->version(),
        ];
    }
}
