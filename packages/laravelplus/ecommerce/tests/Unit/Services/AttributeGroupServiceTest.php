<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Tests\Unit\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use LaravelPlus\Ecommerce\Models\AttributeGroup;
use LaravelPlus\Ecommerce\Services\AttributeGroupService;
use LaravelPlus\Ecommerce\Tests\TestCase;

final class AttributeGroupServiceTest extends TestCase
{
    use RefreshDatabase;

    private AttributeGroupService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = $this->app->make(AttributeGroupService::class);
    }

    public function test_list_returns_paginated_groups(): void
    {
        AttributeGroup::factory()->count(5)->create();

        $result = $this->service->list(3);

        $this->assertCount(3, $result->items());
        $this->assertSame(5, $result->total());
    }

    public function test_list_with_search(): void
    {
        AttributeGroup::factory()->create(['name' => 'Physical Properties']);
        AttributeGroup::factory()->create(['name' => 'Colors']);

        $result = $this->service->list(15, 'Physical');

        $this->assertCount(1, $result->items());
    }

    public function test_create_group(): void
    {
        $group = $this->service->create(['name' => 'New Group']);

        $this->assertInstanceOf(AttributeGroup::class, $group);
        $this->assertSame('New Group', $group->name);
    }

    public function test_update_group(): void
    {
        $group = AttributeGroup::factory()->create(['name' => 'Old']);

        $updated = $this->service->update($group, ['name' => 'New']);

        $this->assertSame('New', $updated->name);
    }

    public function test_delete_group(): void
    {
        $group = AttributeGroup::factory()->create();

        $result = $this->service->delete($group);

        $this->assertTrue($result);
        $this->assertSoftDeleted('ecommerce_attribute_groups', ['id' => $group->id]);
    }

    public function test_get_active(): void
    {
        AttributeGroup::factory()->create(['is_active' => true]);
        AttributeGroup::factory()->inactive()->create();

        $active = $this->service->getActive();

        $this->assertCount(1, $active);
    }
}
