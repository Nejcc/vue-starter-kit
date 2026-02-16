<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Tests\Unit\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use LaravelPlus\Ecommerce\Enums\OrderStatus;
use LaravelPlus\Ecommerce\Models\Order;
use LaravelPlus\Ecommerce\Models\OrderItem;
use LaravelPlus\Ecommerce\Models\Product;
use LaravelPlus\Ecommerce\Services\OrderService;
use LaravelPlus\Ecommerce\Tests\TestCase;
use LaravelPlus\Ecommerce\Tests\User;

final class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    private OrderService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = $this->app->make(OrderService::class);
    }

    public function test_list_returns_paginated_orders(): void
    {
        Order::factory()->count(5)->create();

        $result = $this->service->list(3);

        $this->assertCount(3, $result->items());
        $this->assertSame(5, $result->total());
    }

    public function test_list_with_search_filters_results(): void
    {
        $order = Order::factory()->create();
        Order::factory()->create();

        $result = $this->service->list(15, $order->order_number);

        $this->assertCount(1, $result->items());
    }

    public function test_list_with_status_filters_results(): void
    {
        Order::factory()->create(['status' => OrderStatus::Pending]);
        Order::factory()->confirmed()->create();

        $result = $this->service->list(15, null, OrderStatus::Pending);

        $this->assertCount(1, $result->items());
    }

    public function test_create_order(): void
    {
        $order = $this->service->create([
            'status' => OrderStatus::Pending,
            'subtotal' => 5000,
            'tax' => 500,
            'total' => 5500,
            'currency' => 'USD',
        ]);

        $this->assertInstanceOf(Order::class, $order);
        $this->assertSame(OrderStatus::Pending, $order->status);
    }

    public function test_create_order_with_items(): void
    {
        $product = Product::factory()->create(['price' => 2500]);

        $order = $this->service->create(
            [
                'status' => OrderStatus::Pending,
                'subtotal' => 0,
                'tax' => 0,
                'discount' => 0,
                'shipping_cost' => 0,
                'total' => 0,
                'currency' => 'USD',
            ],
            [
                OrderItem::fromProduct($product, 2),
            ]
        );

        $this->assertCount(1, $order->items);
        $this->assertSame(5000, $order->subtotal);
        $this->assertSame(5000, $order->total);
    }

    public function test_update_status(): void
    {
        $order = Order::factory()->create(['status' => OrderStatus::Pending]);

        $updated = $this->service->updateStatus($order, OrderStatus::Confirmed);

        $this->assertSame(OrderStatus::Confirmed, $updated->status);
    }

    public function test_update_status_sets_completed_at(): void
    {
        $order = Order::factory()->create(['status' => OrderStatus::Delivered]);

        $updated = $this->service->updateStatus($order, OrderStatus::Completed);

        $this->assertNotNull($updated->completed_at);
    }

    public function test_update_status_sets_cancelled_at(): void
    {
        $order = Order::factory()->create(['status' => OrderStatus::Pending]);

        $updated = $this->service->updateStatus($order, OrderStatus::Cancelled);

        $this->assertNotNull($updated->cancelled_at);
    }

    public function test_update_status_throws_on_invalid_transition(): void
    {
        $order = Order::factory()->completed()->create();

        $this->expectException(\InvalidArgumentException::class);

        $this->service->updateStatus($order, OrderStatus::Pending);
    }

    public function test_cancel(): void
    {
        $order = Order::factory()->create(['status' => OrderStatus::Pending]);

        $cancelled = $this->service->cancel($order);

        $this->assertSame(OrderStatus::Cancelled, $cancelled->status);
        $this->assertNotNull($cancelled->cancelled_at);
    }

    public function test_complete(): void
    {
        $order = Order::factory()->create(['status' => OrderStatus::Delivered]);

        $completed = $this->service->complete($order);

        $this->assertSame(OrderStatus::Completed, $completed->status);
        $this->assertNotNull($completed->completed_at);
    }

    public function test_recalculate_totals(): void
    {
        $order = Order::factory()->create([
            'subtotal' => 0,
            'tax' => 500,
            'discount' => 100,
            'shipping_cost' => 1000,
            'total' => 0,
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'quantity' => 2,
            'unit_price' => 2500,
            'total' => 5000,
        ]);

        $recalculated = $this->service->recalculateTotals($order);

        $this->assertSame(5000, $recalculated->subtotal);
        $this->assertSame(6400, $recalculated->total); // 5000 + 500 - 100 + 1000
    }

    public function test_delete(): void
    {
        $order = Order::factory()->create();

        $result = $this->service->delete($order);

        $this->assertTrue($result);
        $this->assertSoftDeleted('ecommerce_orders', ['id' => $order->id]);
    }

    public function test_find_by_uuid(): void
    {
        $order = Order::factory()->create();

        $found = $this->service->findByUuid($order->uuid);

        $this->assertTrue($found->is($order));
    }

    public function test_find_by_uuid_returns_null_when_not_found(): void
    {
        $this->assertNull($this->service->findByUuid('nonexistent'));
    }

    public function test_find_by_order_number(): void
    {
        $order = Order::factory()->create();

        $found = $this->service->findByOrderNumber($order->order_number);

        $this->assertTrue($found->is($order));
    }

    public function test_get_for_user(): void
    {
        $user = User::factory()->create();
        Order::factory()->forUser($user)->count(2)->create();
        Order::factory()->create();

        $orders = $this->service->getForUser($user->id);

        $this->assertCount(2, $orders);
    }

    public function test_get_order_stats(): void
    {
        Order::factory()->create(['status' => OrderStatus::Pending]);
        Order::factory()->completed()->create(['total' => 5000]);
        Order::factory()->completed()->create(['total' => 3000]);

        $stats = $this->service->getOrderStats();

        $this->assertSame(3, $stats['totalOrders']);
        $this->assertSame(1, $stats['pendingOrders']);
        $this->assertSame(2, $stats['completedOrders']);
        $this->assertSame(8000, $stats['revenue']);
    }
}
