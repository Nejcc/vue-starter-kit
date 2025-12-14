<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

/**
 * Dashboard controller for displaying the authenticated user's dashboard.
 */
final class DashboardController extends Controller
{
    /**
     * Create a new dashboard controller instance.
     */
    public function __construct()
    {
        $this->middleware(['auth', 'verified']);
    }

    /**
     * Display the user's dashboard.
     */
    public function index(): Response
    {
        return Inertia::render('Dashboard');
    }
}
