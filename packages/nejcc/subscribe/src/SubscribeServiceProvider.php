<?php

declare(strict_types=1);

namespace Nejcc\Subscribe;

use Illuminate\Support\ServiceProvider;

final class SubscribeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/subscribe.php', 'subscribe');

        $this->app->singleton(SubscribeManager::class, fn ($app) => new SubscribeManager($app));
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/subscribe.php' => config_path('subscribe.php'),
        ], 'subscribe-config');

        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'subscribe-migrations');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        if (config('subscribe.admin.enabled', true)) {
            $this->loadRoutesFrom(__DIR__.'/../routes/admin.php');
        }

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'subscribe');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/subscribe'),
        ], 'subscribe-views');
    }
}
