<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use LaravelPlus\Ecommerce\Enums\OrderStatus;
use LaravelPlus\Ecommerce\Models\Order;
use LaravelPlus\Ecommerce\Models\OrderItem;
use LaravelPlus\Ecommerce\Models\Product;

/**
 * @extends Factory<Order>
 */
final class OrderFactory extends Factory
{
    protected $model = Order::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->numberBetween(1000, 100000);
        $tax = (int) ($subtotal * 0.1);
        $shipping = fake()->randomElement([0, 500, 1000, 1500]);

        return [
            'uuid' => (string) Str::uuid(),
            'order_number' => null, // Auto-generated
            'user_id' => null,
            'status' => OrderStatus::Pending,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'discount' => 0,
            'shipping_cost' => $shipping,
            'total' => $subtotal + $tax + $shipping,
            'currency' => 'USD',
            'shipping_address' => [
                'name' => fake()->name(),
                'address' => fake()->streetAddress(),
                'city' => fake()->city(),
                'state' => fake()->stateAbbr(),
                'zip' => fake()->postcode(),
                'country' => 'US',
            ],
            'billing_address' => null,
            'notes' => null,
            'metadata' => null,
            'placed_at' => now(),
            'completed_at' => null,
            'cancelled_at' => null,
        ];
    }

    public function confirmed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => OrderStatus::Confirmed,
        ]);
    }

    public function processing(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => OrderStatus::Processing,
        ]);
    }

    public function shipped(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => OrderStatus::Shipped,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => OrderStatus::Completed,
            'completed_at' => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => OrderStatus::Cancelled,
            'cancelled_at' => now(),
        ]);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Model  $user
     */
    public function forUser($user): static
    {
        return $this->state(fn (array $attributes): array => [
            'user_id' => $user->id,
        ]);
    }

    public function withItems(int $count = 2): static
    {
        return $this->afterCreating(function (Order $order) use ($count): void {
            $products = Product::factory()->count($count)->create();
            $subtotal = 0;

            foreach ($products as $product) {
                $quantity = fake()->numberBetween(1, 5);
                $total = $product->price * $quantity;
                $subtotal += $total;

                OrderItem::factory()->create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'quantity' => $quantity,
                    'unit_price' => $product->price,
                    'total' => $total,
                ]);
            }

            $tax = (int) ($subtotal * 0.1);
            $order->update([
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $subtotal + $tax + $order->shipping_cost,
            ]);
        });
    }
}
