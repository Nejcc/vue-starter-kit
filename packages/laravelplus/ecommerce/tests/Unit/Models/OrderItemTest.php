<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use LaravelPlus\Ecommerce\Models\Order;
use LaravelPlus\Ecommerce\Models\OrderItem;
use LaravelPlus\Ecommerce\Models\Product;
use LaravelPlus\Ecommerce\Models\ProductVariant;
use LaravelPlus\Ecommerce\Tests\TestCase;

final class OrderItemTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_be_created_with_factory(): void
    {
        $item = OrderItem::factory()->create();

        $this->assertDatabaseHas('ecommerce_order_items', ['id' => $item->id]);
    }

    public function test_it_belongs_to_order(): void
    {
        $order = Order::factory()->create();
        $item = OrderItem::factory()->create(['order_id' => $order->id]);

        $this->assertTrue($item->order->is($order));
    }

    public function test_it_belongs_to_product(): void
    {
        $product = Product::factory()->create();
        $item = OrderItem::factory()->forProduct($product)->create();

        $this->assertTrue($item->product->is($product));
    }

    public function test_it_belongs_to_product_variant(): void
    {
        $variant = ProductVariant::factory()->create();
        $item = OrderItem::factory()->forVariant($variant)->create();

        $this->assertTrue($item->productVariant->is($variant));
    }

    public function test_it_casts_money_fields_to_integer(): void
    {
        $item = OrderItem::factory()->create([
            'unit_price' => 2500,
            'total' => 5000,
            'quantity' => 2,
        ]);

        $this->assertIsInt($item->unit_price);
        $this->assertIsInt($item->total);
        $this->assertIsInt($item->quantity);
    }

    public function test_it_casts_options_to_array(): void
    {
        $item = OrderItem::factory()->create(['options' => ['color' => 'red']]);

        $this->assertIsArray($item->options);
        $this->assertSame('red', $item->options['color']);
    }

    public function test_it_casts_metadata_to_array(): void
    {
        $item = OrderItem::factory()->create(['metadata' => ['note' => 'test']]);

        $this->assertIsArray($item->metadata);
        $this->assertSame('test', $item->metadata['note']);
    }

    public function test_from_product_creates_snapshot(): void
    {
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'price' => 2500,
        ]);

        $data = OrderItem::fromProduct($product, 3);

        $this->assertSame($product->id, $data['product_id']);
        $this->assertSame('Test Product', $data['name']);
        $this->assertSame('TEST-001', $data['sku']);
        $this->assertSame(3, $data['quantity']);
        $this->assertSame(2500, $data['unit_price']);
        $this->assertSame(7500, $data['total']);
    }

    public function test_from_variant_creates_snapshot(): void
    {
        $product = Product::factory()->create(['price' => 2000]);
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'name' => 'Large Red',
            'sku' => 'VAR-001',
            'price' => 2500,
            'options' => ['size' => 'L', 'color' => 'red'],
        ]);

        $data = OrderItem::fromVariant($variant, 2);

        $this->assertSame($variant->product_id, $data['product_id']);
        $this->assertSame($variant->id, $data['product_variant_id']);
        $this->assertSame('Large Red', $data['name']);
        $this->assertSame('VAR-001', $data['sku']);
        $this->assertSame(2, $data['quantity']);
        $this->assertSame($variant->getEffectivePrice(), $data['unit_price']);
        $this->assertSame($variant->getEffectivePrice() * 2, $data['total']);
    }

    public function test_factory_for_order_state(): void
    {
        $order = Order::factory()->create();
        $item = OrderItem::factory()->forOrder($order)->create();

        $this->assertSame($order->id, $item->order_id);
    }
}
