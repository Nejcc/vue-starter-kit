<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Contracts\Services\AuditLogServiceInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class AuditLogsController extends Controller
{
    public function __construct(
        private readonly AuditLogServiceInterface $auditLogService,
    ) {}

    public function index(Request $request): Response
    {
        $logs = $this->auditLogService->getFilteredPaginated(
            $request->get('search'),
            $request->get('event'),
        );

        $eventTypes = $this->auditLogService->getDistinctEventTypes();

        return Inertia::render('admin/AuditLogs/Index', [
            'logs' => $logs,
            'eventTypes' => $eventTypes,
            'filters' => [
                'search' => $request->get('search', ''),
                'event' => $request->get('event', ''),
            ],
        ]);
    }
}
