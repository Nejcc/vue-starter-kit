<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use LaravelPlus\Ecommerce\Models\Product;
use LaravelPlus\Ecommerce\Models\ProductVariant;

/**
 * @extends Factory<ProductVariant>
 */
final class ProductVariantFactory extends Factory
{
    protected $model = ProductVariant::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'name' => fake()->words(2, true),
            'sku' => mb_strtoupper(fake()->unique()->bothify('VAR-???-####')),
            'price' => fake()->numberBetween(100, 100000),
            'compare_at_price' => null,
            'stock_quantity' => fake()->numberBetween(0, 200),
            'options' => ['size' => fake()->randomElement(['S', 'M', 'L', 'XL'])],
            'weight' => fake()->optional()->randomFloat(3, 0.1, 50),
            'images' => null,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }

    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes): array => [
            'stock_quantity' => 0,
        ]);
    }

    public function withOptions(array $options): static
    {
        return $this->state(fn (array $attributes): array => [
            'options' => $options,
        ]);
    }

    public function forProduct(Product $product): static
    {
        return $this->state(fn (array $attributes): array => [
            'product_id' => $product->id,
        ]);
    }
}
