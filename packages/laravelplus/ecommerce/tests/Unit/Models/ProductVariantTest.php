<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use LaravelPlus\Ecommerce\Enums\StockStatus;
use LaravelPlus\Ecommerce\Models\Product;
use LaravelPlus\Ecommerce\Models\ProductVariant;
use LaravelPlus\Ecommerce\Tests\TestCase;

final class ProductVariantTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_be_created_with_factory(): void
    {
        $variant = ProductVariant::factory()->create();

        $this->assertDatabaseHas('ecommerce_product_variants', ['id' => $variant->id]);
    }

    public function test_it_belongs_to_product(): void
    {
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->forProduct($product)->create();

        $this->assertTrue($variant->product->is($product));
    }

    public function test_it_casts_options_to_array(): void
    {
        $variant = ProductVariant::factory()->create([
            'options' => ['color' => 'Red', 'size' => 'L'],
        ]);

        $variant->refresh();

        $this->assertIsArray($variant->options);
        $this->assertSame('Red', $variant->options['color']);
    }

    public function test_get_effective_price_returns_variant_price(): void
    {
        $product = Product::factory()->create(['price' => 5000]);
        $variant = ProductVariant::factory()->forProduct($product)->create(['price' => 3000]);

        $this->assertSame(3000, $variant->getEffectivePrice());
    }

    public function test_get_effective_price_falls_back_to_product_price(): void
    {
        $product = Product::factory()->create(['price' => 5000]);
        $variant = ProductVariant::factory()->forProduct($product)->create(['price' => null]);

        $this->assertSame(5000, $variant->getEffectivePrice());
    }

    public function test_get_stock_status_returns_out_of_stock(): void
    {
        $product = Product::factory()->create(['low_stock_threshold' => 5]);
        $variant = ProductVariant::factory()->forProduct($product)->outOfStock()->create();

        $this->assertSame(StockStatus::OutOfStock, $variant->getStockStatus());
    }

    public function test_get_stock_status_returns_low_stock(): void
    {
        $product = Product::factory()->create(['low_stock_threshold' => 10]);
        $variant = ProductVariant::factory()->forProduct($product)->create(['stock_quantity' => 5]);

        $this->assertSame(StockStatus::LowStock, $variant->getStockStatus());
    }

    public function test_get_stock_status_returns_in_stock(): void
    {
        $product = Product::factory()->create(['low_stock_threshold' => 5]);
        $variant = ProductVariant::factory()->forProduct($product)->create(['stock_quantity' => 100]);

        $this->assertSame(StockStatus::InStock, $variant->getStockStatus());
    }

    public function test_is_in_stock_returns_true_when_stock_positive(): void
    {
        $variant = ProductVariant::factory()->create(['stock_quantity' => 10]);

        $this->assertTrue($variant->isInStock());
    }

    public function test_is_in_stock_returns_false_when_zero(): void
    {
        $variant = ProductVariant::factory()->outOfStock()->create();

        $this->assertFalse($variant->isInStock());
    }

    public function test_formatted_price_from_cents(): void
    {
        $product = Product::factory()->create(['price' => 5000]);
        $variant = ProductVariant::factory()->forProduct($product)->create(['price' => 2999]);

        $this->assertSame('$29.99', $variant->formattedPrice());
    }

    public function test_get_option_returns_specific_option(): void
    {
        $variant = ProductVariant::factory()->create([
            'options' => ['color' => 'Blue', 'size' => 'M'],
        ]);

        $this->assertSame('Blue', $variant->getOption('color'));
        $this->assertNull($variant->getOption('nonexistent'));
    }

    public function test_get_options_label_joins_values(): void
    {
        $variant = ProductVariant::factory()->create([
            'options' => ['color' => 'Red', 'size' => 'L'],
        ]);

        $this->assertSame('Red / L', $variant->getOptionsLabel());
    }

    public function test_get_options_label_returns_name_when_empty(): void
    {
        $variant = ProductVariant::factory()->create([
            'name' => 'Default',
            'options' => [],
        ]);

        $this->assertSame('Default', $variant->getOptionsLabel());
    }

    public function test_scope_active(): void
    {
        $product = Product::factory()->create();
        ProductVariant::factory()->forProduct($product)->active()->create();
        ProductVariant::factory()->forProduct($product)->inactive()->create();

        $this->assertCount(1, ProductVariant::active()->get());
    }

    public function test_scope_in_stock(): void
    {
        $product = Product::factory()->create();
        ProductVariant::factory()->forProduct($product)->create(['stock_quantity' => 10]);
        ProductVariant::factory()->forProduct($product)->outOfStock()->create();

        $this->assertCount(1, ProductVariant::inStock()->get());
    }

    public function test_scope_ordered(): void
    {
        $product = Product::factory()->create();
        ProductVariant::factory()->forProduct($product)->create(['sort_order' => 2, 'name' => 'B']);
        ProductVariant::factory()->forProduct($product)->create(['sort_order' => 1, 'name' => 'A']);

        $ordered = ProductVariant::ordered()->get();

        $this->assertSame('A', $ordered->first()->name);
    }

    public function test_soft_deletes_work(): void
    {
        $variant = ProductVariant::factory()->create();
        $variant->delete();

        $this->assertSoftDeleted('ecommerce_product_variants', ['id' => $variant->id]);
        $this->assertCount(0, ProductVariant::all());
        $this->assertCount(1, ProductVariant::withTrashed()->get());
    }
}
