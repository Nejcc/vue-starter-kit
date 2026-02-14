<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Constants\AuditEvent;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

final class LogAuthenticationEvent implements ShouldHandleEventsAfterCommit
{
    /**
     * Handle the Login event.
     */
    public function handleLogin(Login $event): void
    {
        if ($event->user instanceof User) {
            AuditLog::log(AuditEvent::AUTH_LOGIN, $event->user, null, null, $event->user->id);
        }
    }

    /**
     * Handle the Logout event.
     */
    public function handleLogout(Logout $event): void
    {
        if ($event->user instanceof User && User::find($event->user->id)) {
            AuditLog::log(AuditEvent::AUTH_LOGOUT, $event->user, null, null, $event->user->id);
        }
    }

    /**
     * Handle the Registered event.
     */
    public function handleRegistered(Registered $event): void
    {
        if ($event->user instanceof User) {
            AuditLog::log(AuditEvent::AUTH_REGISTERED, $event->user, null, [
                'name' => $event->user->name,
                'email' => $event->user->email,
            ], $event->user->id);
        }
    }

    /**
     * Handle the PasswordReset event.
     */
    public function handlePasswordReset(PasswordReset $event): void
    {
        if ($event->user instanceof User) {
            AuditLog::log(AuditEvent::AUTH_PASSWORD_RESET, $event->user, null, null, $event->user->id);
        }
    }

    /**
     * Handle the Verified event.
     */
    public function handleVerified(Verified $event): void
    {
        if ($event->user instanceof User) {
            AuditLog::log(AuditEvent::AUTH_EMAIL_VERIFIED, $event->user, null, null, $event->user->id);
        }
    }

    /**
     * Handle the Failed login event.
     */
    public function handleFailed(Failed $event): void
    {
        AuditLog::log(AuditEvent::AUTH_LOGIN_FAILED, null, null, [
            'email' => $event->credentials['email'] ?? 'unknown',
        ]);
    }
}
