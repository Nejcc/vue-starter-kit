<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;

final class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Repository bindings (new instance each time).
     *
     * @var array<class-string, class-string>
     */
    public array $bindings = [
        UserRepositoryInterface::class => UserRepository::class,
        // Add more repository bindings here as needed
    ];

    /**
     * Repository singletons (shared instance).
     *
     * @var array<class-string, class-string>
     */
    public array $singletons = [
        // Add repository singletons here as needed
        // Example: SomeRepositoryInterface::class => SomeRepository::class,
    ];
}
