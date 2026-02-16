<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Tests\Unit\Repositories;

use Illuminate\Foundation\Testing\RefreshDatabase;
use LaravelPlus\Ecommerce\Models\Category;
use LaravelPlus\Ecommerce\Models\Product;
use LaravelPlus\Ecommerce\Repositories\ProductRepository;
use LaravelPlus\Ecommerce\Tests\TestCase;

final class ProductRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private ProductRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new ProductRepository;
    }

    public function test_find(): void
    {
        $product = Product::factory()->create();

        $found = $this->repository->find($product->id);

        $this->assertTrue($found->is($product));
    }

    public function test_find_returns_null_when_not_found(): void
    {
        $this->assertNull($this->repository->find(999));
    }

    public function test_find_or_fail(): void
    {
        $product = Product::factory()->create();

        $found = $this->repository->findOrFail($product->id);

        $this->assertTrue($found->is($product));
    }

    public function test_find_or_fail_throws_exception(): void
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->repository->findOrFail(999);
    }

    public function test_find_by_slug(): void
    {
        $product = Product::factory()->create(['slug' => 'test-product']);

        $found = $this->repository->findBySlug('test-product');

        $this->assertTrue($found->is($product));
    }

    public function test_find_by_sku(): void
    {
        $product = Product::factory()->create(['sku' => 'SKU-001']);

        $found = $this->repository->findBySku('SKU-001');

        $this->assertTrue($found->is($product));
    }

    public function test_create(): void
    {
        $product = $this->repository->create([
            'name' => 'Test',
            'price' => 1000,
        ]);

        $this->assertDatabaseHas('ecommerce_products', ['name' => 'Test']);
    }

    public function test_update(): void
    {
        $product = Product::factory()->create(['name' => 'Old']);

        $updated = $this->repository->update($product, ['name' => 'New']);

        $this->assertSame('New', $updated->name);
    }

    public function test_delete(): void
    {
        $product = Product::factory()->create();

        $this->repository->delete($product);

        $this->assertSoftDeleted('ecommerce_products', ['id' => $product->id]);
    }

    public function test_paginate(): void
    {
        Product::factory()->count(5)->create();

        $result = $this->repository->paginate(3);

        $this->assertCount(3, $result->items());
        $this->assertSame(5, $result->total());
    }

    public function test_search(): void
    {
        Product::factory()->create(['name' => 'Blue Shirt']);
        Product::factory()->create(['name' => 'Red Pants']);

        $result = $this->repository->search('Blue');

        $this->assertCount(1, $result->items());
    }

    public function test_search_by_sku(): void
    {
        Product::factory()->create(['sku' => 'FIND-ME']);
        Product::factory()->create(['sku' => 'OTHER']);

        $result = $this->repository->search('FIND');

        $this->assertCount(1, $result->items());
    }

    public function test_filter_by_category(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create();
        $product->categories()->attach($category);

        Product::factory()->create();

        $result = $this->repository->filterByCategory($category->id);

        $this->assertCount(1, $result->items());
    }

    public function test_get_active(): void
    {
        Product::factory()->active()->count(2)->create();
        Product::factory()->draft()->create();

        $active = $this->repository->getActive();

        $this->assertCount(2, $active);
    }

    public function test_get_featured(): void
    {
        Product::factory()->featured()->create();
        Product::factory()->active()->create();

        $featured = $this->repository->getFeatured();

        $this->assertCount(1, $featured);
    }
}
