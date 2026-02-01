<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\Repositories\AuditLogRepositoryInterface;
use App\Contracts\Repositories\PermissionRepositoryInterface;
use App\Contracts\Repositories\RoleRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Repositories\AuditLogRepository;
use App\Repositories\PermissionRepository;
use App\Repositories\RoleRepository;
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
    ];

    /** @var array<class-string, class-string> */
    public array $singletons = [
        //
    ];
}
