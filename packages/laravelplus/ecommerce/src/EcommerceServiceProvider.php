<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce;

use App\Support\AdminNavigation;
use Illuminate\Support\ServiceProvider;
use LaravelPlus\Ecommerce\Contracts\AttributeGroupRepositoryInterface;
use LaravelPlus\Ecommerce\Contracts\AttributeRepositoryInterface;
use LaravelPlus\Ecommerce\Contracts\CategoryRepositoryInterface;
use LaravelPlus\Ecommerce\Contracts\OrderRepositoryInterface;
use LaravelPlus\Ecommerce\Contracts\ProductRepositoryInterface;
use LaravelPlus\Ecommerce\Contracts\ProductVariantRepositoryInterface;
use LaravelPlus\Ecommerce\Contracts\TagRepositoryInterface;
use LaravelPlus\Ecommerce\Repositories\AttributeGroupRepository;
use LaravelPlus\Ecommerce\Repositories\AttributeRepository;
use LaravelPlus\Ecommerce\Repositories\CategoryRepository;
use LaravelPlus\Ecommerce\Repositories\OrderRepository;
use LaravelPlus\Ecommerce\Repositories\ProductRepository;
use LaravelPlus\Ecommerce\Repositories\ProductVariantRepository;
use LaravelPlus\Ecommerce\Repositories\TagRepository;
use LaravelPlus\Ecommerce\Services\AttributeGroupService;
use LaravelPlus\Ecommerce\Services\AttributeService;
use LaravelPlus\Ecommerce\Services\CategoryService;
use LaravelPlus\Ecommerce\Services\EcommerceService;
use LaravelPlus\Ecommerce\Services\OrderService;
use LaravelPlus\Ecommerce\Services\ProductService;
use LaravelPlus\Ecommerce\Services\ProductVariantService;
use LaravelPlus\Ecommerce\Services\TagService;
use Throwable;

final class EcommerceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/ecommerce.php', 'ecommerce');

        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
        $this->app->bind(ProductVariantRepositoryInterface::class, ProductVariantRepository::class);
        $this->app->bind(TagRepositoryInterface::class, TagRepository::class);
        $this->app->bind(AttributeGroupRepositoryInterface::class, AttributeGroupRepository::class);
        $this->app->bind(AttributeRepositoryInterface::class, AttributeRepository::class);
        $this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);

        $this->app->singleton(ProductService::class, fn ($app) => new ProductService(
            $app->make(ProductRepositoryInterface::class),
        ));

        $this->app->singleton(CategoryService::class, fn ($app) => new CategoryService(
            $app->make(CategoryRepositoryInterface::class),
        ));

        $this->app->singleton(ProductVariantService::class, fn ($app) => new ProductVariantService(
            $app->make(ProductVariantRepositoryInterface::class),
        ));

        $this->app->singleton(TagService::class, fn ($app) => new TagService(
            $app->make(TagRepositoryInterface::class),
        ));

        $this->app->singleton(AttributeGroupService::class, fn ($app) => new AttributeGroupService(
            $app->make(AttributeGroupRepositoryInterface::class),
        ));

        $this->app->singleton(AttributeService::class, fn ($app) => new AttributeService(
            $app->make(AttributeRepositoryInterface::class),
        ));

        $this->app->singleton(OrderService::class, fn ($app) => new OrderService(
            $app->make(OrderRepositoryInterface::class),
        ));

        $this->app->singleton(EcommerceService::class, fn ($app) => new EcommerceService(
            $app->make(ProductService::class),
            $app->make(CategoryService::class),
            $app->make(ProductVariantService::class),
            $app->make(TagService::class),
            $app->make(AttributeGroupService::class),
            $app->make(AttributeService::class),
            $app->make(OrderService::class),
        ));
    }

    public function boot(): void
    {
        $this->registerPublishing();
        $this->registerResources();
        $this->registerRoutes();
        $this->registerAdminNavigation();
    }

    private function registerPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/ecommerce.php' => config_path('ecommerce.php'),
            ], 'ecommerce-config');

            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'ecommerce-migrations');

            $this->publishes([
                __DIR__.'/../skills/ecommerce-development' => base_path('.claude/skills/ecommerce-development'),
            ], 'ecommerce-skills');

            $this->publishes([
                __DIR__.'/../skills/ecommerce-development' => base_path('.github/skills/ecommerce-development'),
            ], 'ecommerce-skills-github');

            // Frontend resources — publishes pages to main app (for overriding)
            $this->publishes([
                __DIR__.'/../resources/js/pages' => resource_path('js/pages'),
            ], 'ecommerce-pages');

            $this->publishes([
                __DIR__.'/../resources/js/composables' => resource_path('js/composables'),
            ], 'ecommerce-composables');

            // Publish all frontend resources at once
            $this->publishes([
                __DIR__.'/../resources/js/pages' => resource_path('js/pages'),
                __DIR__.'/../resources/js/composables' => resource_path('js/composables'),
            ], 'ecommerce-frontend');
        }
    }

    private function registerResources(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    private function registerRoutes(): void
    {
        if ($this->isAdminEnabled()) {
            $this->loadRoutesFrom(__DIR__.'/../routes/admin.php');
        }
    }

    /**
     * Check if admin routes should be enabled via DB setting or config fallback.
     */
    private function isAdminEnabled(): bool
    {
        if (class_exists(\LaravelPlus\GlobalSettings\Models\Setting::class)) {
            try {
                $dbValue = \LaravelPlus\GlobalSettings\Models\Setting::get('package.ecommerce.enabled');

                if ($dbValue !== null) {
                    return in_array($dbValue, ['1', 'true', true, 1], true);
                }
            } catch (Throwable) {
                // Table may not exist yet during migrations
            }
        }

        return (bool) config('ecommerce.admin.enabled', true);
    }

    private function registerAdminNavigation(): void
    {
        $this->callAfterResolving(AdminNavigation::class, function (AdminNavigation $nav): void {
            $prefix = config('ecommerce.admin.prefix', 'admin/ecommerce');

            $nav->register('ecommerce', 'Ecommerce', 'ShoppingCart', [
                ['title' => 'Dashboard', 'href' => "/{$prefix}", 'icon' => 'LayoutDashboard'],
                ['title' => 'Products', 'href' => "/{$prefix}/products", 'icon' => 'Package'],
                ['title' => 'Categories', 'href' => "/{$prefix}/categories", 'icon' => 'FolderTree'],
                ['title' => 'Category Tree', 'href' => "/{$prefix}/categories/tree", 'icon' => 'Network'],
                ['title' => 'Tags', 'href' => "/{$prefix}/tags", 'icon' => 'Tags'],
                ['title' => 'Attributes', 'href' => "/{$prefix}/attributes", 'icon' => 'SlidersHorizontal'],
                ['title' => 'Orders', 'href' => "/{$prefix}/orders", 'icon' => 'ShoppingBag'],
            ], 50);
        });
    }

    /**
     * @return array<int, string>
     */
    public function provides(): array
    {
        return [
            ProductRepositoryInterface::class,
            CategoryRepositoryInterface::class,
            ProductVariantRepositoryInterface::class,
            TagRepositoryInterface::class,
            AttributeGroupRepositoryInterface::class,
            AttributeRepositoryInterface::class,
            OrderRepositoryInterface::class,
            ProductService::class,
            CategoryService::class,
            ProductVariantService::class,
            TagService::class,
            AttributeGroupService::class,
            AttributeService::class,
            OrderService::class,
            EcommerceService::class,
        ];
    }
}
