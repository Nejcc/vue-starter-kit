<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use LaravelPlus\Ecommerce\Enums\ProductStatus;
use LaravelPlus\Ecommerce\Models\Product;

/**
 * @extends Factory<Product>
 */
final class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'name' => ucwords($name),
            'slug' => Str::slug($name),
            'sku' => mb_strtoupper(fake()->unique()->bothify('???-####')),
            'description' => fake()->optional()->paragraph(),
            'short_description' => fake()->optional()->sentence(),
            'price' => fake()->numberBetween(100, 100000),
            'compare_at_price' => null,
            'cost_price' => null,
            'currency' => 'USD',
            'status' => ProductStatus::Draft,
            'stock_quantity' => fake()->numberBetween(0, 500),
            'low_stock_threshold' => 5,
            'is_active' => true,
            'is_featured' => false,
            'is_digital' => false,
            'has_variants' => false,
            'weight' => fake()->optional()->randomFloat(3, 0.1, 50),
            'dimensions' => null,
            'images' => null,
            'metadata' => null,
            'published_at' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => ProductStatus::Active,
            'is_active' => true,
            'published_at' => now()->subDay(),
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => ProductStatus::Draft,
            'published_at' => null,
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => ProductStatus::Archived,
            'is_active' => false,
        ]);
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_featured' => true,
            'status' => ProductStatus::Active,
            'is_active' => true,
            'published_at' => now()->subDay(),
        ]);
    }

    public function digital(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_digital' => true,
            'weight' => null,
            'dimensions' => null,
        ]);
    }

    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes): array => [
            'stock_quantity' => 0,
        ]);
    }

    public function lowStock(): static
    {
        return $this->state(fn (array $attributes): array => [
            'stock_quantity' => 3,
            'low_stock_threshold' => 5,
        ]);
    }

    public function withComparePrice(): static
    {
        return $this->state(fn (array $attributes): array => [
            'compare_at_price' => ($attributes['price'] ?? 5000) + fake()->numberBetween(1000, 5000),
        ]);
    }

    public function withVariants(): static
    {
        return $this->state(fn (array $attributes): array => [
            'has_variants' => true,
        ]);
    }
}
