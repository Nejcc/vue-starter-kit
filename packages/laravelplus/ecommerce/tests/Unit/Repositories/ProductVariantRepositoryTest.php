<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Tests\Unit\Repositories;

use Illuminate\Foundation\Testing\RefreshDatabase;
use LaravelPlus\Ecommerce\Models\Product;
use LaravelPlus\Ecommerce\Models\ProductVariant;
use LaravelPlus\Ecommerce\Repositories\ProductVariantRepository;
use LaravelPlus\Ecommerce\Tests\TestCase;

final class ProductVariantRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private ProductVariantRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new ProductVariantRepository;
    }

    public function test_find(): void
    {
        $variant = ProductVariant::factory()->create();

        $found = $this->repository->find($variant->id);

        $this->assertTrue($found->is($variant));
    }

    public function test_find_returns_null_when_not_found(): void
    {
        $this->assertNull($this->repository->find(999));
    }

    public function test_find_or_fail(): void
    {
        $variant = ProductVariant::factory()->create();

        $found = $this->repository->findOrFail($variant->id);

        $this->assertTrue($found->is($variant));
    }

    public function test_find_or_fail_throws_exception(): void
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->repository->findOrFail(999);
    }

    public function test_create(): void
    {
        $product = Product::factory()->create();

        $variant = $this->repository->create([
            'product_id' => $product->id,
            'name' => 'Red / Large',
            'options' => ['color' => 'Red', 'size' => 'L'],
            'stock_quantity' => 10,
        ]);

        $this->assertDatabaseHas('ecommerce_product_variants', ['name' => 'Red / Large']);
    }

    public function test_update(): void
    {
        $variant = ProductVariant::factory()->create(['name' => 'Old']);

        $updated = $this->repository->update($variant, ['name' => 'New']);

        $this->assertSame('New', $updated->name);
    }

    public function test_delete(): void
    {
        $variant = ProductVariant::factory()->create();

        $this->repository->delete($variant);

        $this->assertSoftDeleted('ecommerce_product_variants', ['id' => $variant->id]);
    }

    public function test_get_for_product(): void
    {
        $product = Product::factory()->create();
        ProductVariant::factory()->count(3)->forProduct($product)->create();

        $otherProduct = Product::factory()->create();
        ProductVariant::factory()->forProduct($otherProduct)->create();

        $variants = $this->repository->getForProduct($product->id);

        $this->assertCount(3, $variants);
    }

    public function test_reorder(): void
    {
        $product = Product::factory()->create();
        $v1 = ProductVariant::factory()->forProduct($product)->create(['sort_order' => 0]);
        $v2 = ProductVariant::factory()->forProduct($product)->create(['sort_order' => 1]);

        $this->repository->reorder([$v1->id => 5, $v2->id => 3]);

        $this->assertSame(5, $v1->fresh()->sort_order);
        $this->assertSame(3, $v2->fresh()->sort_order);
    }
}
