<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use LaravelPlus\Ecommerce\Enums\OrderStatus;
use LaravelPlus\Ecommerce\Models\Order;
use LaravelPlus\Ecommerce\Models\OrderItem;
use LaravelPlus\Ecommerce\Tests\TestCase;
use LaravelPlus\Ecommerce\Tests\User;

final class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_be_created_with_factory(): void
    {
        $order = Order::factory()->create();

        $this->assertDatabaseHas('ecommerce_orders', ['id' => $order->id]);
    }

    public function test_it_auto_generates_uuid(): void
    {
        $order = Order::factory()->create(['uuid' => null]);

        $this->assertNotNull($order->uuid);
        $this->assertNotEmpty($order->uuid);
    }

    public function test_it_auto_generates_order_number(): void
    {
        $order = Order::factory()->create();

        $this->assertNotNull($order->order_number);
        $this->assertStringStartsWith('ORD-', $order->order_number);
    }

    public function test_it_generates_sequential_order_numbers(): void
    {
        $order1 = Order::factory()->create();
        $order2 = Order::factory()->create();

        $num1 = (int) substr($order1->order_number, -4);
        $num2 = (int) substr($order2->order_number, -4);

        $this->assertSame($num1 + 1, $num2);
    }

    public function test_route_key_name_is_uuid(): void
    {
        $order = new Order;

        $this->assertSame('uuid', $order->getRouteKeyName());
    }

    public function test_it_casts_status_to_enum(): void
    {
        $order = Order::factory()->create(['status' => OrderStatus::Pending]);

        $this->assertInstanceOf(OrderStatus::class, $order->status);
        $this->assertSame(OrderStatus::Pending, $order->status);
    }

    public function test_it_casts_money_fields_to_integer(): void
    {
        $order = Order::factory()->create([
            'subtotal' => 5000,
            'tax' => 500,
            'discount' => 100,
            'shipping_cost' => 1000,
            'total' => 6400,
        ]);

        $this->assertIsInt($order->subtotal);
        $this->assertIsInt($order->tax);
        $this->assertIsInt($order->discount);
        $this->assertIsInt($order->shipping_cost);
        $this->assertIsInt($order->total);
    }

    public function test_it_casts_addresses_to_array(): void
    {
        $address = ['name' => 'John', 'city' => 'NYC'];
        $order = Order::factory()->create([
            'shipping_address' => $address,
            'billing_address' => $address,
        ]);

        $this->assertIsArray($order->shipping_address);
        $this->assertIsArray($order->billing_address);
        $this->assertSame('John', $order->shipping_address['name']);
    }

    public function test_it_casts_metadata_to_array(): void
    {
        $order = Order::factory()->create(['metadata' => ['key' => 'value']]);

        $this->assertIsArray($order->metadata);
        $this->assertSame('value', $order->metadata['key']);
    }

    public function test_it_has_many_items(): void
    {
        $order = Order::factory()->create();
        OrderItem::factory()->count(3)->create(['order_id' => $order->id]);

        $this->assertCount(3, $order->items);
    }

    public function test_it_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->forUser($user)->create();

        $this->assertTrue($order->user->is($user));
    }

    public function test_formatted_total(): void
    {
        $order = Order::factory()->create(['total' => 12345]);

        $this->assertSame('$123.45', $order->formattedTotal());
    }

    public function test_can_transition_to(): void
    {
        $order = Order::factory()->create(['status' => OrderStatus::Pending]);

        $this->assertTrue($order->canTransitionTo(OrderStatus::Confirmed));
        $this->assertTrue($order->canTransitionTo(OrderStatus::Cancelled));
        $this->assertFalse($order->canTransitionTo(OrderStatus::Delivered));
    }

    public function test_final_status_cannot_transition(): void
    {
        $order = Order::factory()->completed()->create();

        $this->assertFalse($order->canTransitionTo(OrderStatus::Pending));
        $this->assertFalse($order->canTransitionTo(OrderStatus::Cancelled));
    }

    public function test_scope_for_user(): void
    {
        $user = User::factory()->create();
        Order::factory()->forUser($user)->count(2)->create();
        Order::factory()->create();

        $this->assertCount(2, Order::forUser($user->id)->get());
    }

    public function test_scope_with_status(): void
    {
        Order::factory()->create(['status' => OrderStatus::Pending]);
        Order::factory()->confirmed()->create();
        Order::factory()->create(['status' => OrderStatus::Pending]);

        $this->assertCount(2, Order::withStatus(OrderStatus::Pending)->get());
        $this->assertCount(1, Order::withStatus(OrderStatus::Confirmed)->get());
    }

    public function test_scope_pending(): void
    {
        Order::factory()->create(['status' => OrderStatus::Pending]);
        Order::factory()->confirmed()->create();

        $this->assertCount(1, Order::pending()->get());
    }

    public function test_scope_completed(): void
    {
        Order::factory()->create(['status' => OrderStatus::Pending]);
        Order::factory()->completed()->create();

        $this->assertCount(1, Order::completed()->get());
    }

    public function test_factory_states(): void
    {
        $confirmed = Order::factory()->confirmed()->create();
        $processing = Order::factory()->processing()->create();
        $shipped = Order::factory()->shipped()->create();
        $completed = Order::factory()->completed()->create();
        $cancelled = Order::factory()->cancelled()->create();

        $this->assertSame(OrderStatus::Confirmed, $confirmed->status);
        $this->assertSame(OrderStatus::Processing, $processing->status);
        $this->assertSame(OrderStatus::Shipped, $shipped->status);
        $this->assertSame(OrderStatus::Completed, $completed->status);
        $this->assertNotNull($completed->completed_at);
        $this->assertSame(OrderStatus::Cancelled, $cancelled->status);
        $this->assertNotNull($cancelled->cancelled_at);
    }

    public function test_factory_with_items(): void
    {
        $order = Order::factory()->withItems(3)->create();

        $this->assertCount(3, $order->items);
        $this->assertGreaterThan(0, $order->subtotal);
    }

    public function test_soft_deletes(): void
    {
        $order = Order::factory()->create();

        $order->delete();

        $this->assertSoftDeleted('ecommerce_orders', ['id' => $order->id]);
        $this->assertNotNull(Order::withTrashed()->find($order->id));
    }
}
