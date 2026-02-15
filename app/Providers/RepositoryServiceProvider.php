<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\Repositories\AdminNotificationRepositoryInterface;
use App\Contracts\Repositories\AuditLogRepositoryInterface;
use App\Contracts\Repositories\CacheManagementRepositoryInterface;
use App\Contracts\Repositories\CookieConsentRepositoryInterface;
use App\Contracts\Repositories\DatabaseBrowserRepositoryInterface;
use App\Contracts\Repositories\DataExportRepositoryInterface;
use App\Contracts\Repositories\FailedJobRepositoryInterface;
use App\Contracts\Repositories\PermissionRepositoryInterface;
use App\Contracts\Repositories\RoleRepositoryInterface;
use App\Contracts\Repositories\SessionRepositoryInterface;
use App\Contracts\Repositories\SystemHealthRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Repositories\AdminNotificationRepository;
use App\Repositories\AuditLogRepository;
use App\Repositories\CacheManagementRepository;
use App\Repositories\CookieConsentRepository;
use App\Repositories\DatabaseBrowserRepository;
use App\Repositories\DataExportRepository;
use App\Repositories\FailedJobRepository;
use App\Repositories\PermissionRepository;
use App\Repositories\RoleRepository;
use App\Repositories\SessionRepository;
use App\Repositories\SystemHealthRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;

final class RepositoryServiceProvider extends ServiceProvider
{
    /** @var array<class-string, class-string> */
    public array $bindings = [
        UserRepositoryInterface::class => UserRepository::class,
        RoleRepositoryInterface::class => RoleRepository::class,
        PermissionRepositoryInterface::class => PermissionRepository::class,
        AuditLogRepositoryInterface::class => AuditLogRepository::class,
        FailedJobRepositoryInterface::class => FailedJobRepository::class,
        SessionRepositoryInterface::class => SessionRepository::class,
        SystemHealthRepositoryInterface::class => SystemHealthRepository::class,
        CacheManagementRepositoryInterface::class => CacheManagementRepository::class,
        AdminNotificationRepositoryInterface::class => AdminNotificationRepository::class,
        DataExportRepositoryInterface::class => DataExportRepository::class,
        CookieConsentRepositoryInterface::class => CookieConsentRepository::class,
        DatabaseBrowserRepositoryInterface::class => DatabaseBrowserRepository::class,
    ];

    /** @var array<class-string, class-string> */
    public array $singletons = [
        //
    ];
}
