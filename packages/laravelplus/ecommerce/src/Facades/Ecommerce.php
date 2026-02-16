<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Facades;

use Illuminate\Support\Facades\Facade;
use LaravelPlus\Ecommerce\Services\EcommerceService;

/**
 * @method static \Illuminate\Database\Eloquent\Collection getActiveProducts()
 * @method static \Illuminate\Database\Eloquent\Collection getFeaturedProducts()
 * @method static \Illuminate\Database\Eloquent\Collection getCategoryTree()
 * @method static array getStats()
 * @method static string formatPrice(int $cents, ?string $currency = null)
 *
 * @see EcommerceService
 */
final class Ecommerce extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return EcommerceService::class;
    }
}
