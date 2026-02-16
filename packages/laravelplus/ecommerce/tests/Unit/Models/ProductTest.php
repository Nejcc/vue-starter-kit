<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use LaravelPlus\Ecommerce\Enums\ProductStatus;
use LaravelPlus\Ecommerce\Enums\StockStatus;
use LaravelPlus\Ecommerce\Models\Category;
use LaravelPlus\Ecommerce\Models\Product;
use LaravelPlus\Ecommerce\Models\ProductVariant;
use LaravelPlus\Ecommerce\Tests\TestCase;

final class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_be_created_with_factory(): void
    {
        $product = Product::factory()->create();

        $this->assertDatabaseHas('ecommerce_products', ['id' => $product->id]);
    }

    public function test_it_casts_status_to_enum(): void
    {
        $product = Product::factory()->create(['status' => 'active']);

        $this->assertInstanceOf(ProductStatus::class, $product->status);
        $this->assertSame(ProductStatus::Active, $product->status);
    }

    public function test_it_casts_json_fields(): void
    {
        $product = Product::factory()->create([
            'dimensions' => ['length' => 10, 'width' => 5, 'height' => 3],
            'images' => ['image1.jpg', 'image2.jpg'],
            'metadata' => ['key' => 'value'],
        ]);

        $product->refresh();

        $this->assertIsArray($product->dimensions);
        $this->assertIsArray($product->images);
        $this->assertIsArray($product->metadata);
        $this->assertSame(10, $product->dimensions['length']);
    }

    public function test_it_casts_boolean_fields(): void
    {
        $product = Product::factory()->create([
            'is_active' => true,
            'is_featured' => false,
            'is_digital' => true,
            'has_variants' => false,
        ]);

        $this->assertTrue($product->is_active);
        $this->assertFalse($product->is_featured);
        $this->assertTrue($product->is_digital);
        $this->assertFalse($product->has_variants);
    }

    public function test_it_belongs_to_many_categories(): void
    {
        $product = Product::factory()->create();
        $category = Category::factory()->create();

        $product->categories()->attach($category);

        $this->assertCount(1, $product->categories);
        $this->assertTrue($product->categories->contains($category));
    }

    public function test_it_has_many_variants(): void
    {
        $product = Product::factory()->withVariants()->create();
        ProductVariant::factory()->count(3)->forProduct($product)->create();

        $this->assertCount(3, $product->variants);
    }

    public function test_it_auto_generates_slug(): void
    {
        $product = Product::factory()->create(['name' => 'My Test Product', 'slug' => null]);

        $this->assertSame('my-test-product', $product->slug);
    }

    public function test_it_generates_unique_slug(): void
    {
        Product::factory()->create(['slug' => 'test-product']);
        $product = Product::factory()->create(['name' => 'Test Product', 'slug' => null]);

        $this->assertSame('test-product-1', $product->slug);
    }

    public function test_route_key_name_is_slug(): void
    {
        $product = new Product;

        $this->assertSame('slug', $product->getRouteKeyName());
    }

    public function test_stock_status_returns_out_of_stock_when_zero(): void
    {
        $product = Product::factory()->outOfStock()->create();

        $this->assertSame(StockStatus::OutOfStock, $product->getStockStatus());
        $this->assertFalse($product->isInStock());
    }

    public function test_stock_status_returns_low_stock_when_below_threshold(): void
    {
        $product = Product::factory()->lowStock()->create();

        $this->assertSame(StockStatus::LowStock, $product->getStockStatus());
        $this->assertTrue($product->isInStock());
    }

    public function test_stock_status_returns_in_stock_when_above_threshold(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 100, 'low_stock_threshold' => 5]);

        $this->assertSame(StockStatus::InStock, $product->getStockStatus());
        $this->assertTrue($product->isInStock());
    }

    public function test_stock_status_uses_variants_when_has_variants(): void
    {
        $product = Product::factory()->withVariants()->create();
        ProductVariant::factory()->forProduct($product)->create(['stock_quantity' => 0]);

        $this->assertSame(StockStatus::OutOfStock, $product->getStockStatus());
    }

    public function test_is_on_sale_returns_true_when_compare_at_price_is_higher(): void
    {
        $product = Product::factory()->withComparePrice()->create(['price' => 1000]);

        $this->assertTrue($product->isOnSale());
    }

    public function test_is_on_sale_returns_false_without_compare_price(): void
    {
        $product = Product::factory()->create(['compare_at_price' => null]);

        $this->assertFalse($product->isOnSale());
    }

    public function test_is_published_requires_active_status_and_published_at(): void
    {
        $product = Product::factory()->active()->create();

        $this->assertTrue($product->isPublished());
    }

    public function test_is_published_returns_false_for_drafts(): void
    {
        $product = Product::factory()->draft()->create();

        $this->assertFalse($product->isPublished());
    }

    public function test_formatted_price_from_cents(): void
    {
        $product = Product::factory()->create(['price' => 2999]);

        $this->assertSame('$29.99', $product->formattedPrice());
    }

    public function test_formatted_compare_at_price_returns_null_when_not_set(): void
    {
        $product = Product::factory()->create(['compare_at_price' => null]);

        $this->assertNull($product->formattedCompareAtPrice());
    }

    public function test_formatted_compare_at_price_from_cents(): void
    {
        $product = Product::factory()->create(['compare_at_price' => 5999]);

        $this->assertSame('$59.99', $product->formattedCompareAtPrice());
    }

    public function test_get_effective_price_returns_product_price_without_variants(): void
    {
        $product = Product::factory()->create(['price' => 2000]);

        $this->assertSame(2000, $product->getEffectivePrice());
    }

    public function test_get_effective_price_returns_lowest_variant_price(): void
    {
        $product = Product::factory()->withVariants()->create(['price' => 5000]);
        ProductVariant::factory()->forProduct($product)->create(['price' => 3000]);
        ProductVariant::factory()->forProduct($product)->create(['price' => 4000]);

        $this->assertSame(3000, $product->getEffectivePrice());
    }

    public function test_get_total_stock_returns_product_stock_without_variants(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 50]);

        $this->assertSame(50, $product->getTotalStock());
    }

    public function test_get_total_stock_sums_variant_stock(): void
    {
        $product = Product::factory()->withVariants()->create();
        ProductVariant::factory()->forProduct($product)->create(['stock_quantity' => 10]);
        ProductVariant::factory()->forProduct($product)->create(['stock_quantity' => 20]);

        $this->assertSame(30, $product->getTotalStock());
    }

    public function test_scope_active(): void
    {
        Product::factory()->create(['is_active' => true]);
        Product::factory()->create(['is_active' => false]);

        $this->assertCount(1, Product::active()->get());
    }

    public function test_scope_featured(): void
    {
        Product::factory()->featured()->create();
        Product::factory()->create(['is_featured' => false]);

        $this->assertCount(1, Product::featured()->get());
    }

    public function test_scope_published(): void
    {
        Product::factory()->active()->create();
        Product::factory()->draft()->create();

        $this->assertCount(1, Product::published()->get());
    }

    public function test_scope_in_stock(): void
    {
        Product::factory()->create(['stock_quantity' => 10]);
        Product::factory()->outOfStock()->create();

        $this->assertCount(1, Product::inStock()->get());
    }

    public function test_scope_in_category(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create();
        $product->categories()->attach($category);

        Product::factory()->create(); // no category

        $this->assertCount(1, Product::inCategory($category->id)->get());
    }

    public function test_soft_deletes_work(): void
    {
        $product = Product::factory()->create();
        $product->delete();

        $this->assertSoftDeleted('ecommerce_products', ['id' => $product->id]);
        $this->assertCount(0, Product::all());
        $this->assertCount(1, Product::withTrashed()->get());
    }
}
