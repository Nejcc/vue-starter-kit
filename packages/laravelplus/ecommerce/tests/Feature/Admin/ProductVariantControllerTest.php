<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use LaravelPlus\Ecommerce\Models\Product;
use LaravelPlus\Ecommerce\Models\ProductVariant;
use LaravelPlus\Ecommerce\Tests\TestCase;
use LaravelPlus\Ecommerce\Tests\User;

final class ProductVariantControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->product = Product::factory()->withVariants()->create();
    }

    public function test_store_creates_variant(): void
    {
        $response = $this->actingAs($this->user)
            ->post("/admin/ecommerce/products/{$this->product->slug}/variants", [
                'name' => 'Red / Large',
                'sku' => 'TEST-RED-L',
                'price' => 2999,
                'stock_quantity' => 10,
                'options' => ['color' => 'Red', 'size' => 'L'],
            ]);

        $response->assertRedirect(route('admin.ecommerce.products.edit', $this->product));
        $this->assertDatabaseHas('ecommerce_product_variants', ['name' => 'Red / Large']);
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user)
            ->post("/admin/ecommerce/products/{$this->product->slug}/variants", []);

        $response->assertSessionHasErrors(['name', 'options']);
    }

    public function test_update_modifies_variant(): void
    {
        $variant = ProductVariant::factory()->forProduct($this->product)->create(['name' => 'Old']);

        $response = $this->actingAs($this->user)
            ->put("/admin/ecommerce/products/{$this->product->slug}/variants/{$variant->id}", [
                'name' => 'Updated',
                'options' => $variant->options,
            ]);

        $response->assertRedirect(route('admin.ecommerce.products.edit', $this->product));
        $this->assertDatabaseHas('ecommerce_product_variants', ['id' => $variant->id, 'name' => 'Updated']);
    }

    public function test_destroy_deletes_variant(): void
    {
        $variant = ProductVariant::factory()->forProduct($this->product)->create();

        $response = $this->actingAs($this->user)
            ->delete("/admin/ecommerce/products/{$this->product->slug}/variants/{$variant->id}");

        $response->assertRedirect(route('admin.ecommerce.products.edit', $this->product));
        $this->assertSoftDeleted('ecommerce_product_variants', ['id' => $variant->id]);
    }

    public function test_reorder_variants(): void
    {
        $v1 = ProductVariant::factory()->forProduct($this->product)->create(['sort_order' => 0]);
        $v2 = ProductVariant::factory()->forProduct($this->product)->create(['sort_order' => 1]);

        $response = $this->actingAs($this->user)
            ->post("/admin/ecommerce/products/{$this->product->slug}/variants/reorder", [
                'order' => [$v1->id => 2, $v2->id => 1],
            ]);

        $response->assertRedirect(route('admin.ecommerce.products.edit', $this->product));
        $this->assertSame(2, $v1->fresh()->sort_order);
        $this->assertSame(1, $v2->fresh()->sort_order);
    }
}
