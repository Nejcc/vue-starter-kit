<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

/**
 * About controller for displaying the about page.
 */
final class AboutController extends Controller
{
    /**
     * Display the about page.
     */
    public function index(): Response
    {
        return Inertia::render('About');
    }
}
