<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use LaravelPlus\Ecommerce\Enums\AttributeType;
use LaravelPlus\Ecommerce\Models\Attribute;
use LaravelPlus\Ecommerce\Models\AttributeGroup;

/**
 * @extends Factory<Attribute>
 */
final class AttributeFactory extends Factory
{
    protected $model = Attribute::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'attribute_group_id' => AttributeGroup::factory(),
            'name' => ucwords($name),
            'slug' => Str::slug($name),
            'type' => AttributeType::Text,
            'sort_order' => fake()->numberBetween(0, 100),
            'is_filterable' => false,
            'is_required' => false,
            'is_active' => true,
            'values' => null,
        ];
    }

    /**
     * @param  array<int, string>  $options
     */
    public function selectType(array $options = ['Small', 'Medium', 'Large']): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => AttributeType::Select,
            'values' => $options,
        ]);
    }

    public function colorType(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => AttributeType::Color,
        ]);
    }

    public function numberType(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => AttributeType::Number,
        ]);
    }

    public function booleanType(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => AttributeType::Boolean,
        ]);
    }

    public function filterable(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_filterable' => true,
        ]);
    }

    public function required(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_required' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }

    public function forGroup(?AttributeGroup $group = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'attribute_group_id' => $group?->id ?? AttributeGroup::factory(),
        ]);
    }
}
