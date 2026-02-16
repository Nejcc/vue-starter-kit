<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Tests\Unit\Repositories;

use Illuminate\Foundation\Testing\RefreshDatabase;
use LaravelPlus\Ecommerce\Models\AttributeGroup;
use LaravelPlus\Ecommerce\Repositories\AttributeGroupRepository;
use LaravelPlus\Ecommerce\Tests\TestCase;

final class AttributeGroupRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private AttributeGroupRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new AttributeGroupRepository;
    }

    public function test_find(): void
    {
        $group = AttributeGroup::factory()->create();

        $found = $this->repository->find($group->id);

        $this->assertTrue($found->is($group));
    }

    public function test_find_returns_null_when_not_found(): void
    {
        $this->assertNull($this->repository->find(999));
    }

    public function test_find_by_slug(): void
    {
        $group = AttributeGroup::factory()->create(['slug' => 'test-group']);

        $found = $this->repository->findBySlug('test-group');

        $this->assertTrue($found->is($group));
    }

    public function test_create(): void
    {
        $group = $this->repository->create(['name' => 'Test Group']);

        $this->assertDatabaseHas('ecommerce_attribute_groups', ['name' => 'Test Group']);
    }

    public function test_update(): void
    {
        $group = AttributeGroup::factory()->create(['name' => 'Old']);

        $updated = $this->repository->update($group, ['name' => 'New']);

        $this->assertSame('New', $updated->name);
    }

    public function test_delete(): void
    {
        $group = AttributeGroup::factory()->create();

        $this->repository->delete($group);

        $this->assertSoftDeleted('ecommerce_attribute_groups', ['id' => $group->id]);
    }

    public function test_paginate(): void
    {
        AttributeGroup::factory()->count(5)->create();

        $result = $this->repository->paginate(3);

        $this->assertCount(3, $result->items());
        $this->assertSame(5, $result->total());
    }

    public function test_search(): void
    {
        AttributeGroup::factory()->create(['name' => 'Physical Properties']);
        AttributeGroup::factory()->create(['name' => 'Color Options']);

        $result = $this->repository->search('Physical');

        $this->assertCount(1, $result->items());
    }

    public function test_get_active(): void
    {
        AttributeGroup::factory()->create(['is_active' => true]);
        AttributeGroup::factory()->inactive()->create();

        $active = $this->repository->getActive();

        $this->assertCount(1, $active);
    }
}
