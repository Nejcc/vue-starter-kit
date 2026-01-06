<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Constants\RoleNames;
use App\Http\Controllers\Controller;
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

        return Inertia::render('admin/Dashboard');
    }
}
