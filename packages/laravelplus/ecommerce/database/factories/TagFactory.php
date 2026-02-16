<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use LaravelPlus\Ecommerce\Models\Tag;

/**
 * @extends Factory<Tag>
 */
final class TagFactory extends Factory
{
    protected $model = Tag::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => ucwords($name),
            'slug' => Str::slug($name),
            'type' => null,
            'sort_order' => fake()->numberBetween(0, 100),
        ];
    }

    public function productType(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => 'product',
        ]);
    }

    public function sorted(int $order): static
    {
        return $this->state(fn (array $attributes): array => [
            'sort_order' => $order,
        ]);
    }
}
