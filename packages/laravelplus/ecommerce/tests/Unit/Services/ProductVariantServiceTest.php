<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Tests\Unit\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use LaravelPlus\Ecommerce\Models\Product;
use LaravelPlus\Ecommerce\Models\ProductVariant;
use LaravelPlus\Ecommerce\Services\ProductVariantService;
use LaravelPlus\Ecommerce\Tests\TestCase;

final class ProductVariantServiceTest extends TestCase
{
    use RefreshDatabase;

    private ProductVariantService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = $this->app->make(ProductVariantService::class);
    }

    public function test_get_for_product_returns_variants(): void
    {
        $product = Product::factory()->withVariants()->create();
        ProductVariant::factory()->count(3)->forProduct($product)->create();

        $variants = $this->service->getForProduct($product->id);

        $this->assertCount(3, $variants);
    }

    public function test_create_variant(): void
    {
        $product = Product::factory()->create(['has_variants' => false]);

        $variant = $this->service->create($product, [
            'name' => 'Red / Large',
            'sku' => 'TEST-RED-L',
            'price' => 2999,
            'stock_quantity' => 10,
            'options' => ['color' => 'Red', 'size' => 'L'],
        ]);

        $this->assertInstanceOf(ProductVariant::class, $variant);
        $this->assertSame($product->id, $variant->product_id);
        $this->assertTrue($product->fresh()->has_variants);
    }

    public function test_create_variant_sets_has_variants_on_product(): void
    {
        $product = Product::factory()->create(['has_variants' => false]);

        $this->service->create($product, [
            'name' => 'Variant',
            'options' => ['size' => 'S'],
        ]);

        $this->assertTrue($product->fresh()->has_variants);
    }

    public function test_update_variant(): void
    {
        $variant = ProductVariant::factory()->create(['name' => 'Old Name']);

        $updated = $this->service->update($variant, ['name' => 'New Name']);

        $this->assertSame('New Name', $updated->name);
    }

    public function test_delete_variant(): void
    {
        $product = Product::factory()->withVariants()->create();
        $variant = ProductVariant::factory()->forProduct($product)->create();

        $result = $this->service->delete($variant);

        $this->assertTrue($result);
        $this->assertSoftDeleted('ecommerce_product_variants', ['id' => $variant->id]);
    }

    public function test_delete_last_variant_unsets_has_variants(): void
    {
        $product = Product::factory()->withVariants()->create();
        $variant = ProductVariant::factory()->forProduct($product)->create();

        $this->service->delete($variant);

        $this->assertFalse($product->fresh()->has_variants);
    }

    public function test_reorder(): void
    {
        $product = Product::factory()->create();
        $v1 = ProductVariant::factory()->forProduct($product)->create(['sort_order' => 0]);
        $v2 = ProductVariant::factory()->forProduct($product)->create(['sort_order' => 1]);

        $this->service->reorder([$v1->id => 2, $v2->id => 1]);

        $this->assertSame(2, $v1->fresh()->sort_order);
        $this->assertSame(1, $v2->fresh()->sort_order);
    }

    public function test_update_stock(): void
    {
        $variant = ProductVariant::factory()->create(['stock_quantity' => 10]);

        $updated = $this->service->updateStock($variant, 50);

        $this->assertSame(50, $updated->stock_quantity);
    }

    public function test_increment_stock(): void
    {
        $variant = ProductVariant::factory()->create(['stock_quantity' => 10]);

        $updated = $this->service->incrementStock($variant, 5);

        $this->assertSame(15, $updated->stock_quantity);
    }

    public function test_decrement_stock(): void
    {
        $variant = ProductVariant::factory()->create(['stock_quantity' => 10]);

        $updated = $this->service->decrementStock($variant, 3);

        $this->assertSame(7, $updated->stock_quantity);
    }

    public function test_decrement_stock_does_not_go_below_zero(): void
    {
        $variant = ProductVariant::factory()->create(['stock_quantity' => 2]);

        $updated = $this->service->decrementStock($variant, 5);

        $this->assertSame(0, $updated->stock_quantity);
    }
}
