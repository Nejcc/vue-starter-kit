<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Tests\Unit\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use LaravelPlus\Ecommerce\Enums\ProductStatus;
use LaravelPlus\Ecommerce\Models\Category;
use LaravelPlus\Ecommerce\Models\Product;
use LaravelPlus\Ecommerce\Services\ProductService;
use LaravelPlus\Ecommerce\Tests\TestCase;

final class ProductServiceTest extends TestCase
{
    use RefreshDatabase;

    private ProductService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = $this->app->make(ProductService::class);
    }

    public function test_list_returns_paginated_products(): void
    {
        Product::factory()->count(5)->create();

        $result = $this->service->list(3);

        $this->assertCount(3, $result->items());
        $this->assertSame(5, $result->total());
    }

    public function test_list_with_search_filters_results(): void
    {
        Product::factory()->create(['name' => 'Blue T-Shirt']);
        Product::factory()->create(['name' => 'Red Pants']);

        $result = $this->service->list(15, 'Blue');

        $this->assertCount(1, $result->items());
        $this->assertSame('Blue T-Shirt', $result->items()[0]->name);
    }

    public function test_list_with_category_filter(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create();
        $product->categories()->attach($category);

        Product::factory()->create(); // not in category

        $result = $this->service->list(15, null, $category->id);

        $this->assertCount(1, $result->items());
    }

    public function test_create_product(): void
    {
        $data = [
            'name' => 'Test Product',
            'price' => 2999,
            'status' => ProductStatus::Draft->value,
        ];

        $product = $this->service->create($data);

        $this->assertInstanceOf(Product::class, $product);
        $this->assertSame('Test Product', $product->name);
        $this->assertSame(2999, $product->price);
    }

    public function test_create_product_with_categories(): void
    {
        $categories = Category::factory()->count(2)->create();

        $product = $this->service->create([
            'name' => 'Test Product',
            'price' => 1000,
            'category_ids' => $categories->pluck('id')->toArray(),
        ]);

        $this->assertCount(2, $product->categories);
    }

    public function test_update_product(): void
    {
        $product = Product::factory()->create(['name' => 'Old Name']);

        $updated = $this->service->update($product, ['name' => 'New Name', 'price' => $product->price]);

        $this->assertSame('New Name', $updated->name);
    }

    public function test_update_product_syncs_categories(): void
    {
        $product = Product::factory()->create();
        $categories = Category::factory()->count(2)->create();
        $product->categories()->attach($categories->first());

        $this->service->update($product, [
            'name' => $product->name,
            'price' => $product->price,
            'category_ids' => [$categories->last()->id],
        ]);

        $this->assertCount(1, $product->fresh()->categories);
        $this->assertTrue($product->fresh()->categories->contains($categories->last()));
    }

    public function test_delete_product(): void
    {
        $product = Product::factory()->create();

        $result = $this->service->delete($product);

        $this->assertTrue($result);
        $this->assertSoftDeleted('ecommerce_products', ['id' => $product->id]);
    }

    public function test_update_stock(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 10]);

        $updated = $this->service->updateStock($product, 50);

        $this->assertSame(50, $updated->stock_quantity);
    }

    public function test_increment_stock(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 10]);

        $updated = $this->service->incrementStock($product, 5);

        $this->assertSame(15, $updated->stock_quantity);
    }

    public function test_decrement_stock(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 10]);

        $updated = $this->service->decrementStock($product, 3);

        $this->assertSame(7, $updated->stock_quantity);
    }

    public function test_decrement_stock_does_not_go_below_zero(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 2]);

        $updated = $this->service->decrementStock($product, 5);

        $this->assertSame(0, $updated->stock_quantity);
    }

    public function test_publish(): void
    {
        $product = Product::factory()->draft()->create();

        $published = $this->service->publish($product);

        $this->assertSame(ProductStatus::Active, $published->status);
        $this->assertTrue($published->is_active);
        $this->assertNotNull($published->published_at);
    }

    public function test_unpublish(): void
    {
        $product = Product::factory()->active()->create();

        $unpublished = $this->service->unpublish($product);

        $this->assertSame(ProductStatus::Draft, $unpublished->status);
    }

    public function test_toggle_featured(): void
    {
        $product = Product::factory()->create(['is_featured' => false]);

        $toggled = $this->service->toggleFeatured($product);

        $this->assertTrue($toggled->is_featured);

        $toggled = $this->service->toggleFeatured($toggled);

        $this->assertFalse($toggled->is_featured);
    }

    public function test_sync_categories(): void
    {
        $product = Product::factory()->create();
        $categories = Category::factory()->count(3)->create();

        $this->service->syncCategories($product, $categories->pluck('id')->toArray());

        $this->assertCount(3, $product->categories);
    }

    public function test_get_active_products(): void
    {
        Product::factory()->active()->count(2)->create();
        Product::factory()->draft()->create();

        $active = $this->service->getActive();

        $this->assertCount(2, $active);
    }

    public function test_get_featured_products(): void
    {
        Product::factory()->featured()->create();
        Product::factory()->active()->create();

        $featured = $this->service->getFeatured();

        $this->assertCount(1, $featured);
    }

    public function test_find_by_slug(): void
    {
        $product = Product::factory()->create(['slug' => 'test-slug']);

        $found = $this->service->findBySlug('test-slug');

        $this->assertTrue($found->is($product));
    }

    public function test_find_by_sku(): void
    {
        $product = Product::factory()->create(['sku' => 'TEST-001']);

        $found = $this->service->findBySku('TEST-001');

        $this->assertTrue($found->is($product));
    }

    public function test_find_by_slug_returns_null_when_not_found(): void
    {
        $this->assertNull($this->service->findBySlug('nonexistent'));
    }
}
