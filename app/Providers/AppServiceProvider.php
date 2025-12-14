<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\Services\UserServiceInterface;
use App\Listeners\UpdateLastLoginAt;
use App\Services\UserService;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Service bindings
        $this->app->bind(UserServiceInterface::class, UserService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(Login::class, UpdateLastLoginAt::class);
    }
}
