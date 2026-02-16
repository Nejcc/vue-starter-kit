<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use LaravelPlus\Ecommerce\Enums\AttributeType;
use LaravelPlus\Ecommerce\Models\Attribute;
use LaravelPlus\Ecommerce\Models\AttributeGroup;
use LaravelPlus\Ecommerce\Models\Product;
use LaravelPlus\Ecommerce\Tests\TestCase;

final class AttributeTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_be_created_with_factory(): void
    {
        $attribute = Attribute::factory()->create();

        $this->assertDatabaseHas('ecommerce_attributes', ['id' => $attribute->id]);
    }

    public function test_it_casts_type_to_enum(): void
    {
        $attribute = Attribute::factory()->create(['type' => 'text']);

        $this->assertInstanceOf(AttributeType::class, $attribute->type);
        $this->assertSame(AttributeType::Text, $attribute->type);
    }

    public function test_it_casts_boolean_fields(): void
    {
        $attribute = Attribute::factory()->filterable()->required()->create();

        $this->assertTrue($attribute->is_filterable);
        $this->assertTrue($attribute->is_required);
        $this->assertTrue($attribute->is_active);
    }

    public function test_it_casts_values_to_array(): void
    {
        $attribute = Attribute::factory()->selectType(['Red', 'Blue', 'Green'])->create();

        $this->assertIsArray($attribute->values);
        $this->assertSame(['Red', 'Blue', 'Green'], $attribute->values);
    }

    public function test_it_auto_generates_slug(): void
    {
        $attribute = Attribute::factory()->create(['name' => 'Fabric Type', 'slug' => null]);

        $this->assertSame('fabric-type', $attribute->slug);
    }

    public function test_route_key_name_is_slug(): void
    {
        $attribute = new Attribute;

        $this->assertSame('slug', $attribute->getRouteKeyName());
    }

    public function test_it_belongs_to_group(): void
    {
        $group = AttributeGroup::factory()->create();
        $attribute = Attribute::factory()->forGroup($group)->create();

        $this->assertTrue($attribute->group->is($group));
    }

    public function test_it_belongs_to_many_products(): void
    {
        $attribute = Attribute::factory()->create();
        $product = Product::factory()->create();

        $product->attributes()->attach($attribute, ['value' => 'test']);

        $this->assertCount(1, $attribute->products);
        $this->assertSame('test', $attribute->products->first()->pivot->value);
    }

    public function test_scope_active(): void
    {
        Attribute::factory()->create(['is_active' => true]);
        Attribute::factory()->inactive()->create();

        $this->assertCount(1, Attribute::active()->get());
    }

    public function test_scope_ordered(): void
    {
        $group = AttributeGroup::factory()->create();
        Attribute::factory()->forGroup($group)->create(['sort_order' => 3, 'name' => 'Charlie']);
        Attribute::factory()->forGroup($group)->create(['sort_order' => 1, 'name' => 'Alpha']);

        $attributes = Attribute::ordered()->get();

        $this->assertSame('Alpha', $attributes[0]->name);
    }

    public function test_scope_filterable(): void
    {
        Attribute::factory()->filterable()->create();
        Attribute::factory()->create(['is_filterable' => false]);

        $this->assertCount(1, Attribute::filterable()->get());
    }

    public function test_soft_deletes_work(): void
    {
        $attribute = Attribute::factory()->create();
        $attribute->delete();

        $this->assertSoftDeleted('ecommerce_attributes', ['id' => $attribute->id]);
    }

    public function test_factory_select_type_state(): void
    {
        $attribute = Attribute::factory()->selectType(['S', 'M', 'L'])->create();

        $this->assertSame(AttributeType::Select, $attribute->type);
        $this->assertSame(['S', 'M', 'L'], $attribute->values);
    }

    public function test_factory_color_type_state(): void
    {
        $attribute = Attribute::factory()->colorType()->create();

        $this->assertSame(AttributeType::Color, $attribute->type);
    }

    public function test_factory_number_type_state(): void
    {
        $attribute = Attribute::factory()->numberType()->create();

        $this->assertSame(AttributeType::Number, $attribute->type);
    }

    public function test_factory_boolean_type_state(): void
    {
        $attribute = Attribute::factory()->booleanType()->create();

        $this->assertSame(AttributeType::Boolean, $attribute->type);
    }
}
