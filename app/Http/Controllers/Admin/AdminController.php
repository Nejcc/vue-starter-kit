<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Contracts\Services\AuditLogServiceInterface;
use App\Contracts\Services\PermissionServiceInterface;
use App\Contracts\Services\RoleServiceInterface;
use App\Contracts\Services\UserServiceInterface;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

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
            'recentUsers' => $recentUsers,
            'recentActivity' => $recentActivity,
        ]);
    }
}
