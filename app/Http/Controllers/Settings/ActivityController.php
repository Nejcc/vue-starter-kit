<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Contracts\Services\AuditLogServiceInterface;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class ActivityController extends Controller
{
    public function __construct(
        private readonly AuditLogServiceInterface $auditLogService,
    ) {}

    /**
     * Show the user's activity log.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        $logs = $this->auditLogService
            ->getUserActivityPaginated($user->id)
            ->through(fn (AuditLog $log): array => [
                'id' => $log->id,
                'event' => $log->event,
                'description' => $this->auditLogService->describeEvent($log->event),
                'ip_address' => $log->ip_address,
                'user_agent' => $log->user_agent,
                'created_at' => $log->created_at->toIso8601String(),
                'created_at_human' => $log->created_at->diffForHumans(),
            ]);

        return Inertia::render('settings/Activity', [
            'logs' => $logs,
        ]);
    }
}
