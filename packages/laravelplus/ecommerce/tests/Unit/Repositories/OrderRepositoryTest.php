<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Tests\Unit\Repositories;

use Illuminate\Foundation\Testing\RefreshDatabase;
use LaravelPlus\Ecommerce\Enums\OrderStatus;
use LaravelPlus\Ecommerce\Models\Order;
use LaravelPlus\Ecommerce\Repositories\OrderRepository;
use LaravelPlus\Ecommerce\Tests\TestCase;
use LaravelPlus\Ecommerce\Tests\User;

final class OrderRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private OrderRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new OrderRepository;
    }

    public function test_find(): void
    {
        $order = Order::factory()->create();

        $found = $this->repository->find($order->id);

        $this->assertTrue($found->is($order));
    }

    public function test_find_returns_null_when_not_found(): void
    {
        $this->assertNull($this->repository->find(999));
    }

    public function test_find_or_fail(): void
    {
        $order = Order::factory()->create();

        $found = $this->repository->findOrFail($order->id);

        $this->assertTrue($found->is($order));
    }

    public function test_find_or_fail_throws_exception(): void
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->repository->findOrFail(999);
    }

    public function test_find_by_uuid(): void
    {
        $order = Order::factory()->create();

        $found = $this->repository->findByUuid($order->uuid);

        $this->assertTrue($found->is($order));
    }

    public function test_find_by_uuid_returns_null_when_not_found(): void
    {
        $this->assertNull($this->repository->findByUuid('nonexistent'));
    }

    public function test_find_by_order_number(): void
    {
        $order = Order::factory()->create();

        $found = $this->repository->findByOrderNumber($order->order_number);

        $this->assertTrue($found->is($order));
    }

    public function test_create(): void
    {
        $order = $this->repository->create([
            'status' => OrderStatus::Pending,
            'subtotal' => 5000,
            'tax' => 500,
            'total' => 5500,
            'currency' => 'USD',
        ]);

        $this->assertDatabaseHas('ecommerce_orders', ['id' => $order->id]);
        $this->assertNotNull($order->uuid);
        $this->assertNotNull($order->order_number);
    }

    public function test_update(): void
    {
        $order = Order::factory()->create(['status' => OrderStatus::Pending]);

        $updated = $this->repository->update($order, ['status' => OrderStatus::Confirmed]);

        $this->assertSame(OrderStatus::Confirmed, $updated->status);
    }

    public function test_delete(): void
    {
        $order = Order::factory()->create();

        $this->repository->delete($order);

        $this->assertSoftDeleted('ecommerce_orders', ['id' => $order->id]);
    }

    public function test_paginate(): void
    {
        Order::factory()->count(5)->create();

        $result = $this->repository->paginate(3);

        $this->assertCount(3, $result->items());
        $this->assertSame(5, $result->total());
    }

    public function test_search_by_order_number(): void
    {
        $order = Order::factory()->create();
        Order::factory()->create();

        $result = $this->repository->search($order->order_number);

        $this->assertCount(1, $result->items());
        $this->assertTrue($result->items()[0]->is($order));
    }

    public function test_search_by_user_name(): void
    {
        $user = User::factory()->create(['name' => 'John Doe']);
        Order::factory()->forUser($user)->create();
        Order::factory()->create();

        $result = $this->repository->search('John');

        $this->assertCount(1, $result->items());
    }

    public function test_search_by_user_email(): void
    {
        $user = User::factory()->create(['email' => 'unique-test@example.com']);
        Order::factory()->forUser($user)->create();
        Order::factory()->create();

        $result = $this->repository->search('unique-test@example');

        $this->assertCount(1, $result->items());
    }

    public function test_filter_by_status(): void
    {
        Order::factory()->create(['status' => OrderStatus::Pending]);
        Order::factory()->confirmed()->create();
        Order::factory()->create(['status' => OrderStatus::Pending]);

        $result = $this->repository->filterByStatus(OrderStatus::Pending);

        $this->assertCount(2, $result->items());
    }

    public function test_get_for_user(): void
    {
        $user = User::factory()->create();
        Order::factory()->forUser($user)->count(3)->create();
        Order::factory()->create();

        $orders = $this->repository->getForUser($user->id);

        $this->assertCount(3, $orders);
    }
}
