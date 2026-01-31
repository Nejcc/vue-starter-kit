<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Constants\RoleNames;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Admin dashboard controller.
 *
 * Handles the main admin dashboard.
 */
final class AdminController extends Controller
{
    /**
     * Create a new admin controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Check if user has admin or super-admin role.
     */
    private function authorizeAdmin(): void
    {
        $user = auth()->user();

        if (!$user || (!$user->hasRole(RoleNames::SUPER_ADMIN) && !$user->hasRole(RoleNames::ADMIN))) {
            abort(403, 'Unauthorized. Admin access required.');
        }
    }

    /**
     * Show the admin dashboard.
     *
     * @return Response The Inertia response with dashboard data
     */
    public function index(): Response
    {
        $this->authorizeAdmin();

        $stats = [
            'totalUsers' => User::query()->count(),
            'verifiedUsers' => User::query()->whereNotNull('email_verified_at')->count(),
            'totalRoles' => Role::query()->count(),
            'totalPermissions' => Permission::query()->count(),
        ];

        $recentUsers = User::query()
            ->latest()
            ->limit(5)
            ->get(['id', 'name', 'email', 'created_at']);

        $recentActivity = AuditLog::query()
            ->with('user:id,name,email')
            ->latest()
            ->limit(10)
            ->get(['id', 'user_id', 'event', 'auditable_type', 'auditable_id', 'created_at']);

        return Inertia::render('admin/Dashboard', [
            'stats' => $stats,
            'recentUsers' => $recentUsers,
            'recentActivity' => $recentActivity,
        ]);
    }
}
