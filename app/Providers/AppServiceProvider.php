<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\Services\AdminNotificationServiceInterface;
use App\Contracts\Services\AuditLogServiceInterface;
use App\Contracts\Services\CacheManagementServiceInterface;
use App\Contracts\Services\CookieConsentServiceInterface;
use App\Contracts\Services\DatabaseBrowserServiceInterface;
use App\Contracts\Services\DataExportServiceInterface;
use App\Contracts\Services\FailedJobServiceInterface;
use App\Contracts\Services\ImpersonationServiceInterface;
use App\Contracts\Services\NotificationServiceInterface;
use App\Contracts\Services\PermissionServiceInterface;
use App\Contracts\Services\RoleServiceInterface;
use App\Contracts\Services\SessionManagementServiceInterface;
use App\Contracts\Services\SystemHealthServiceInterface;
use App\Contracts\Services\UserServiceInterface;
use App\Listeners\LogAuthenticationEvent;
use App\Listeners\UpdateLastLoginAt;
use App\Services\AdminNotificationService;
use App\Services\AuditLogService;
use App\Services\CacheManagementService;
use App\Services\CookieConsentService;
use App\Services\DatabaseBrowserService;
use App\Services\DataExportService;
use App\Services\FailedJobService;
use App\Services\ImpersonationService;
use App\Services\NotificationService;
use App\Services\PermissionService;
use App\Services\RoleService;
use App\Services\SessionManagementService;
use App\Services\SystemHealthService;
use App\Services\UserService;
use App\Support\AdminNavigation;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AdminNavigation::class);

        // Service bindings
        $this->app->bind(UserServiceInterface::class, UserService::class);
        $this->app->bind(RoleServiceInterface::class, RoleService::class);
        $this->app->bind(PermissionServiceInterface::class, PermissionService::class);
        $this->app->bind(NotificationServiceInterface::class, NotificationService::class);
        $this->app->bind(AuditLogServiceInterface::class, AuditLogService::class);
        $this->app->bind(ImpersonationServiceInterface::class, ImpersonationService::class);
        $this->app->bind(FailedJobServiceInterface::class, FailedJobService::class);
        $this->app->bind(SessionManagementServiceInterface::class, SessionManagementService::class);
        $this->app->bind(SystemHealthServiceInterface::class, SystemHealthService::class);
        $this->app->bind(CacheManagementServiceInterface::class, CacheManagementService::class);
        $this->app->bind(AdminNotificationServiceInterface::class, AdminNotificationService::class);
        $this->app->bind(DataExportServiceInterface::class, DataExportService::class);
        $this->app->bind(CookieConsentServiceInterface::class, CookieConsentService::class);
        $this->app->bind(DatabaseBrowserServiceInterface::class, DatabaseBrowserService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(Login::class, UpdateLastLoginAt::class);

        // Authentication audit logging
        $authListener = LogAuthenticationEvent::class;
        Event::listen(Login::class, [$authListener, 'handleLogin']);
        Event::listen(Logout::class, [$authListener, 'handleLogout']);
        Event::listen(Registered::class, [$authListener, 'handleRegistered']);
        Event::listen(PasswordReset::class, [$authListener, 'handlePasswordReset']);
        Event::listen(Verified::class, [$authListener, 'handleVerified']);
        Event::listen(Failed::class, [$authListener, 'handleFailed']);

        $this->configureDefaults();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null
        );
    }
}
