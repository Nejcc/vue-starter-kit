<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Tests\Unit\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use LaravelPlus\Ecommerce\Models\Category;
use LaravelPlus\Ecommerce\Models\Product;
use LaravelPlus\Ecommerce\Services\EcommerceService;
use LaravelPlus\Ecommerce\Tests\TestCase;

final class EcommerceServiceTest extends TestCase
{
    use RefreshDatabase;

    private EcommerceService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = $this->app->make(EcommerceService::class);
    }

    public function test_get_active_products(): void
    {
        Product::factory()->active()->count(2)->create();
        Product::factory()->draft()->create();

        $active = $this->service->getActiveProducts();

        $this->assertCount(2, $active);
    }

    public function test_get_featured_products(): void
    {
        Product::factory()->featured()->create();
        Product::factory()->active()->create();

        $featured = $this->service->getFeaturedProducts();

        $this->assertCount(1, $featured);
    }

    public function test_get_category_tree(): void
    {
        $root = Category::factory()->rootCategory()->create();
        Category::factory()->create(['parent_id' => $root->id]);

        $tree = $this->service->getCategoryTree();

        $this->assertCount(1, $tree);
    }

    public function test_get_stats(): void
    {
        Product::factory()->active()->count(3)->create();
        Product::factory()->featured()->create();
        Product::factory()->draft()->create();
        Category::factory()->count(2)->create();

        $stats = $this->service->getStats();

        $this->assertSame(5, $stats['total_products']);
        $this->assertSame(4, $stats['active_products']);
        $this->assertSame(2, $stats['total_categories']);
        $this->assertSame(1, $stats['featured_products']);
    }

    public function test_format_price(): void
    {
        $this->assertSame('$29.99', $this->service->formatPrice(2999));
        $this->assertSame('$0.00', $this->service->formatPrice(0));
        $this->assertSame('$100.00', $this->service->formatPrice(10000));
    }
}
