<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use LaravelPlus\Ecommerce\Enums\OrderStatus;
use LaravelPlus\Ecommerce\Models\Order;
use LaravelPlus\Ecommerce\Tests\TestCase;
use LaravelPlus\Ecommerce\Tests\User;

final class OrderControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_index_requires_authentication(): void
    {
        $response = $this->get('/admin/ecommerce/orders');

        $response->assertRedirect('/login');
    }

    public function test_index_displays_orders(): void
    {
        Order::factory()->count(3)->create();

        $response = $this->actingAs($this->user)
            ->get('/admin/ecommerce/orders');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('admin/ecommerce/Orders')
            ->has('orders.data', 3)
            ->has('statuses')
            ->has('filters')
        );
    }

    public function test_index_with_search(): void
    {
        $order = Order::factory()->create();
        Order::factory()->create();

        $response = $this->actingAs($this->user)
            ->get('/admin/ecommerce/orders?search='.$order->order_number);

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('admin/ecommerce/Orders')
            ->has('orders.data', 1)
        );
    }

    public function test_index_with_status_filter(): void
    {
        Order::factory()->create(['status' => OrderStatus::Pending]);
        Order::factory()->confirmed()->create();

        $response = $this->actingAs($this->user)
            ->get('/admin/ecommerce/orders?status=pending');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('admin/ecommerce/Orders')
            ->has('orders.data', 1)
        );
    }

    public function test_show_displays_order(): void
    {
        $order = Order::factory()->withItems(2)->create();

        $response = $this->actingAs($this->user)
            ->get("/admin/ecommerce/orders/{$order->uuid}");

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('admin/ecommerce/Orders/Show')
            ->has('order')
            ->has('availableTransitions')
        );
    }

    public function test_update_status(): void
    {
        $order = Order::factory()->create(['status' => OrderStatus::Pending]);

        $response = $this->actingAs($this->user)
            ->patch("/admin/ecommerce/orders/{$order->uuid}/status", [
                'status' => 'confirmed',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('ecommerce_orders', [
            'id' => $order->id,
            'status' => 'confirmed',
        ]);
    }

    public function test_update_status_validates_required_fields(): void
    {
        $order = Order::factory()->create();

        $response = $this->actingAs($this->user)
            ->patch("/admin/ecommerce/orders/{$order->uuid}/status", []);

        $response->assertSessionHasErrors(['status']);
    }

    public function test_update_status_validates_valid_status(): void
    {
        $order = Order::factory()->create();

        $response = $this->actingAs($this->user)
            ->patch("/admin/ecommerce/orders/{$order->uuid}/status", [
                'status' => 'invalid_status',
            ]);

        $response->assertSessionHasErrors(['status']);
    }

    public function test_update_status_rejects_invalid_transition(): void
    {
        $order = Order::factory()->completed()->create();

        $response = $this->actingAs($this->user)
            ->patch("/admin/ecommerce/orders/{$order->uuid}/status", [
                'status' => 'pending',
            ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['status']);
    }

    public function test_destroy_soft_deletes_order(): void
    {
        $order = Order::factory()->create();

        $response = $this->actingAs($this->user)
            ->delete("/admin/ecommerce/orders/{$order->uuid}");

        $response->assertRedirect(route('admin.ecommerce.orders.index'));
        $this->assertSoftDeleted('ecommerce_orders', ['id' => $order->id]);
    }
}
