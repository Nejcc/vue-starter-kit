<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Contracts\Services\SystemHealthServiceInterface;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

final class HealthController extends Controller
{
    public function __construct(
        private readonly SystemHealthServiceInterface $healthService,
    ) {}

    public function index(): Response
    {
        return Inertia::render('admin/Health/Index', [
            'checks' => $this->healthService->runAllChecks(),
            'system' => $this->healthService->getSystemInfo(),
        ]);
    }
}
