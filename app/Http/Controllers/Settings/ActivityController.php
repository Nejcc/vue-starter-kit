<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Constants\AuditEvent;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class ActivityController extends Controller
{
    /**
     * Show the user's activity log.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        $logs = AuditLog::query()
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(20)
            ->through(fn (AuditLog $log): array => [
                'id' => $log->id,
                'event' => $log->event,
                'description' => $this->describeEvent($log->event),
                'ip_address' => $log->ip_address,
                'user_agent' => $log->user_agent,
                'created_at' => $log->created_at->toIso8601String(),
                'created_at_human' => $log->created_at->diffForHumans(),
            ]);

        return Inertia::render('settings/Activity', [
            'logs' => $logs,
        ]);
    }

    /**
     * Get a human-readable description for an audit event.
     */
    private function describeEvent(string $event): string
    {
        return match ($event) {
            AuditEvent::AUTH_LOGIN => 'Signed in',
            AuditEvent::AUTH_LOGOUT => 'Signed out',
            AuditEvent::AUTH_REGISTERED => 'Account created',
            AuditEvent::AUTH_PASSWORD_RESET => 'Password reset via email',
            AuditEvent::AUTH_EMAIL_VERIFIED => 'Email address verified',
            AuditEvent::AUTH_LOGIN_FAILED => 'Failed sign-in attempt',
            AuditEvent::USER_PROFILE_UPDATED => 'Profile information updated',
            AuditEvent::USER_PASSWORD_CHANGED => 'Password changed',
            AuditEvent::USER_ACCOUNT_DELETED => 'Account deleted',
            AuditEvent::IMPERSONATION_STARTED => 'Impersonation started',
            AuditEvent::IMPERSONATION_STOPPED => 'Impersonation stopped',
            default => ucfirst(str_replace(['.', '_'], ' ', $event)),
        };
    }
}
