<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Services;

use Illuminate\Database\Eloquent\Collection;
use LaravelPlus\Ecommerce\Models\Category;
use LaravelPlus\Ecommerce\Models\Product;

/**
 * Main ecommerce facade service.
 *
 * Delegates to Product/Category/Variant services and provides convenience methods.
 */
final class EcommerceService
{
    public function __construct(
        private(set) ProductService $productService,
        private(set) CategoryService $categoryService,
        private(set) ProductVariantService $variantService,
        private(set) TagService $tagService,
        private(set) AttributeGroupService $attributeGroupService,
        private(set) AttributeService $attributeService,
        private(set) OrderService $orderService,
    ) {}

    /**
     * Get active/published products.
     *
     * @return Collection<int, Product>
     */
    public function getActiveProducts(): Collection
    {
        return $this->productService->getActive();
    }

    /**
     * Get featured products.
     *
     * @return Collection<int, Product>
     */
    public function getFeaturedProducts(): Collection
    {
        return $this->productService->getFeatured();
    }

    /**
     * Get the category tree.
     *
     * @return Collection<int, Category>
     */
    public function getCategoryTree(): Collection
    {
        return $this->categoryService->getTree();
    }

    /**
     * Get dashboard stats.
     *
     * @return array{total_products: int, active_products: int, total_categories: int, featured_products: int}
     */
    public function getStats(): array
    {
        return [
            'total_products' => Product::query()->count(),
            'active_products' => Product::query()->published()->count(),
            'total_categories' => Category::query()->count(),
            'featured_products' => Product::query()->published()->featured()->count(),
        ];
    }

    /**
     * Format a price from cents to display string.
     */
    public function formatPrice(int $cents, ?string $currency = null): string
    {
        $decimals = (int) config('ecommerce.currency.decimals', 2);
        $symbol = (string) config('ecommerce.currency.symbol', '$');

        return $symbol.number_format($cents / (10 ** $decimals), $decimals);
    }
}
