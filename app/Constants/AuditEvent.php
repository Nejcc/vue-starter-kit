<?php

declare(strict_types=1);

namespace App\Constants;

/**
 * Audit event type constants used throughout the application.
 *
 * These constants should be used instead of magic strings when logging
 * audit events via AuditLog::log().
 */
final class AuditEvent
{
    // Authentication events
    public const AUTH_LOGIN = 'auth.login';

    public const AUTH_LOGOUT = 'auth.logout';

    public const AUTH_REGISTERED = 'auth.registered';

    public const AUTH_PASSWORD_RESET = 'auth.password_reset';

    public const AUTH_EMAIL_VERIFIED = 'auth.email_verified';

    public const AUTH_LOGIN_FAILED = 'auth.login_failed';

    // User self-service events
    public const USER_PROFILE_UPDATED = 'user.profile_updated';

    public const USER_PASSWORD_CHANGED = 'user.password_changed';

    public const USER_ACCOUNT_DELETED = 'user.account_deleted';

    // Admin user management events
    public const USER_CREATED = 'user.created';

    public const USER_UPDATED = 'user.updated';

    public const USER_DELETED = 'user.deleted';

    public const USER_SUSPENDED = 'user.suspended';

    public const USER_UNSUSPENDED = 'user.unsuspended';

    public const USER_PERMISSIONS_SYNCED = 'user.permissions_synced';

    // Role events
    public const ROLE_CREATED = 'role.created';

    public const ROLE_UPDATED = 'role.updated';

    public const ROLE_DELETED = 'role.deleted';

    public const ROLE_PERMISSIONS_SYNCED = 'role.permissions_synced';

    // Permission events
    public const PERMISSION_CREATED = 'permission.created';

    public const PERMISSION_UPDATED = 'permission.updated';

    public const PERMISSION_DELETED = 'permission.deleted';

    // Impersonation events
    public const IMPERSONATION_STARTED = 'impersonation.started';

    public const IMPERSONATION_STOPPED = 'impersonation.stopped';

    // Admin action events
    public const DATABASE_VIEWED = 'database.viewed';

    public const PACKAGE_TOGGLED = 'package.toggled';

    public const CACHE_CLEARED = 'cache.cleared';

    public const MAINTENANCE_TOGGLED = 'maintenance.toggled';

    public const FAILED_JOB_RETRIED = 'failed_job.retried';

    public const FAILED_JOB_DELETED = 'failed_job.deleted';
}
