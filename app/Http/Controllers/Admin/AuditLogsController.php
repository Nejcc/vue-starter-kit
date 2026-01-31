<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Constants\RoleNames;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Admin audit logs controller.
 *
 * Handles displaying audit log entries.
 */
final class AuditLogsController extends Controller
{
    /**
     * Create a new audit logs controller instance.
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
     * Display a listing of audit logs.
     *
     * @param  Request  $request  The incoming request
     * @return Response The Inertia response with audit logs page data
     */
    public function index(Request $request): Response
    {
        $this->authorizeAdmin();

        $query = AuditLog::query()->with('user:id,name,email');

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search): void {
                $q->where('event', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search): void {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('event')) {
            $query->where('event', $request->get('event'));
        }

        $logs = $query->latest()->paginate(25)->withQueryString();

        $eventTypes = AuditLog::query()
            ->select('event')
            ->distinct()
            ->orderBy('event')
            ->pluck('event');

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
