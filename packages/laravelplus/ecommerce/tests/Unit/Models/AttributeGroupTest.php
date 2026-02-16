<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use LaravelPlus\Ecommerce\Models\Attribute;
use LaravelPlus\Ecommerce\Models\AttributeGroup;
use LaravelPlus\Ecommerce\Tests\TestCase;

final class AttributeGroupTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_be_created_with_factory(): void
    {
        $group = AttributeGroup::factory()->create();

        $this->assertDatabaseHas('ecommerce_attribute_groups', ['id' => $group->id]);
    }

    public function test_it_casts_boolean_fields(): void
    {
        $group = AttributeGroup::factory()->create(['is_active' => true]);

        $this->assertTrue($group->is_active);
    }

    public function test_it_auto_generates_slug(): void
    {
        $group = AttributeGroup::factory()->create(['name' => 'Physical Properties', 'slug' => null]);

        $this->assertSame('physical-properties', $group->slug);
    }

    public function test_it_generates_unique_slug(): void
    {
        AttributeGroup::factory()->create(['slug' => 'test-group']);
        $group = AttributeGroup::factory()->create(['name' => 'Test Group', 'slug' => null]);

        $this->assertSame('test-group-1', $group->slug);
    }

    public function test_route_key_name_is_slug(): void
    {
        $group = new AttributeGroup;

        $this->assertSame('slug', $group->getRouteKeyName());
    }

    public function test_it_has_many_attributes(): void
    {
        $group = AttributeGroup::factory()->create();
        Attribute::factory()->count(3)->forGroup($group)->create();

        $this->assertCount(3, $group->attributes);
    }

    public function test_scope_active(): void
    {
        AttributeGroup::factory()->create(['is_active' => true]);
        AttributeGroup::factory()->inactive()->create();

        $this->assertCount(1, AttributeGroup::active()->get());
    }

    public function test_scope_ordered(): void
    {
        AttributeGroup::factory()->create(['sort_order' => 3, 'name' => 'Charlie']);
        AttributeGroup::factory()->create(['sort_order' => 1, 'name' => 'Alpha']);

        $groups = AttributeGroup::ordered()->get();

        $this->assertSame('Alpha', $groups[0]->name);
        $this->assertSame('Charlie', $groups[1]->name);
    }

    public function test_soft_deletes_work(): void
    {
        $group = AttributeGroup::factory()->create();
        $group->delete();

        $this->assertSoftDeleted('ecommerce_attribute_groups', ['id' => $group->id]);
        $this->assertCount(0, AttributeGroup::all());
        $this->assertCount(1, AttributeGroup::withTrashed()->get());
    }

    public function test_factory_inactive_state(): void
    {
        $group = AttributeGroup::factory()->inactive()->create();

        $this->assertFalse($group->is_active);
    }
}
