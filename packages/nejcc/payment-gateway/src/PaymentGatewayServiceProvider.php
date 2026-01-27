<?php

declare(strict_types=1);

namespace Nejcc\PaymentGateway;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Nejcc\PaymentGateway\Console\Commands\CleanupExpiredSubscriptionsCommand;
use Nejcc\PaymentGateway\Console\Commands\InstallCommand;
use Nejcc\PaymentGateway\Console\Commands\SendTrialEndingRemindersCommand;
use Nejcc\PaymentGateway\Console\Commands\SyncPlansCommand;
use Nejcc\PaymentGateway\Contracts\PaymentGatewayContract;
use Nejcc\PaymentGateway\Events\PaymentWebhookReceived;
use Nejcc\PaymentGateway\Listeners\HandlePayPalWebhook;
use Nejcc\PaymentGateway\Listeners\HandleStripeWebhook;
use Nejcc\PaymentGateway\Listeners\SendPaymentNotifications;

final class PaymentGatewayServiceProvider extends ServiceProvider
{
    /**
     * Event listener mappings.
     *
     * @var array<class-string, array<class-string>>
     */
    protected array $listen = [
        PaymentWebhookReceived::class => [
            HandleStripeWebhook::class,
            HandlePayPalWebhook::class,
        ],
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/payment-gateway.php',
            'payment-gateway'
        );

        // Register the manager as singleton
        $this->app->singleton('payment', fn ($app) => new PaymentGatewayManager($app));

        // Alias for the contract
        $this->app->alias('payment', PaymentGatewayManager::class);

        // Bind the contract to the default driver
        $this->app->bind(PaymentGatewayContract::class, fn ($app) => $app->make('payment')->driver());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerEventListeners();
        $this->registerPublishing();
        $this->registerResources();
        $this->registerCommands();
    }

    /**
     * Register event listeners.
     */
    protected function registerEventListeners(): void
    {
        // Register webhook listeners
        foreach ($this->listen as $event => $listeners) {
            foreach ($listeners as $listener) {
                Event::listen($event, $listener);
            }
        }

        // Register notification subscriber
        Event::subscribe(SendPaymentNotifications::class);
    }

    /**
     * Register publishing.
     */
    protected function registerPublishing(): void
    {
        $this->publishes([
            __DIR__.'/../config/payment-gateway.php' => config_path('payment-gateway.php'),
        ], 'payment-gateway-config');

        $this->publishes([
            __DIR__.'/../database/migrations/' => database_path('migrations'),
        ], 'payment-gateway-migrations');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/payment-gateway'),
        ], 'payment-gateway-views');
    }

    /**
     * Register package resources.
     */
    protected function registerResources(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadRoutesFrom(__DIR__.'/../routes/webhooks.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'payment-gateway');

        // Load admin routes if enabled
        if (config('payment-gateway.admin.enabled', true)) {
            $this->loadRoutesFrom(__DIR__.'/../routes/admin.php');
        }
    }

    /**
     * Register console commands.
     */
    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
                SyncPlansCommand::class,
                SendTrialEndingRemindersCommand::class,
                CleanupExpiredSubscriptionsCommand::class,
            ]);
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array<string>
     */
    public function provides(): array
    {
        return [
            'payment',
            PaymentGatewayManager::class,
            PaymentGatewayContract::class,
        ];
    }
}
