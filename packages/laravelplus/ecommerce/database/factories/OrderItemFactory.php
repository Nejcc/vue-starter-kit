<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use LaravelPlus\Ecommerce\Models\Order;
use LaravelPlus\Ecommerce\Models\OrderItem;
use LaravelPlus\Ecommerce\Models\Product;
use LaravelPlus\Ecommerce\Models\ProductVariant;

/**
 * @extends Factory<OrderItem>
 */
final class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 10);
        $unitPrice = fake()->numberBetween(100, 50000);

        return [
            'order_id' => Order::factory(),
            'product_id' => null,
            'product_variant_id' => null,
            'name' => fake()->words(3, true),
            'sku' => mb_strtoupper(fake()->unique()->bothify('???-####')),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total' => $unitPrice * $quantity,
            'options' => null,
            'metadata' => null,
        ];
    }

    public function forOrder(?Order $order = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'order_id' => $order?->id ?? Order::factory(),
        ]);
    }

    public function forProduct(?Product $product = null): static
    {
        return $this->state(function (array $attributes) use ($product): array {
            $product ??= Product::factory()->create();

            return [
                'product_id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'unit_price' => $product->price,
                'total' => $product->price * ($attributes['quantity'] ?? 1),
            ];
        });
    }

    public function forVariant(?ProductVariant $variant = null): static
    {
        return $this->state(function (array $attributes) use ($variant): array {
            $variant ??= ProductVariant::factory()->create();

            return [
                'product_id' => $variant->product_id,
                'product_variant_id' => $variant->id,
                'name' => $variant->name,
                'sku' => $variant->sku,
                'unit_price' => $variant->getEffectivePrice(),
                'total' => $variant->getEffectivePrice() * ($attributes['quantity'] ?? 1),
                'options' => $variant->options,
            ];
        });
    }
}
