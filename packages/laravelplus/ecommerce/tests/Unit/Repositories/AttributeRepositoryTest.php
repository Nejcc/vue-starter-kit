<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Tests\Unit\Repositories;

use Illuminate\Foundation\Testing\RefreshDatabase;
use LaravelPlus\Ecommerce\Models\Attribute;
use LaravelPlus\Ecommerce\Models\AttributeGroup;
use LaravelPlus\Ecommerce\Repositories\AttributeRepository;
use LaravelPlus\Ecommerce\Tests\TestCase;

final class AttributeRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private AttributeRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new AttributeRepository;
    }

    public function test_find(): void
    {
        $attribute = Attribute::factory()->create();

        $found = $this->repository->find($attribute->id);

        $this->assertTrue($found->is($attribute));
    }

    public function test_find_returns_null_when_not_found(): void
    {
        $this->assertNull($this->repository->find(999));
    }

    public function test_create(): void
    {
        $group = AttributeGroup::factory()->create();

        $attribute = $this->repository->create([
            'attribute_group_id' => $group->id,
            'name' => 'Test Attribute',
            'type' => 'text',
        ]);

        $this->assertDatabaseHas('ecommerce_attributes', ['name' => 'Test Attribute']);
    }

    public function test_update(): void
    {
        $attribute = Attribute::factory()->create(['name' => 'Old']);

        $updated = $this->repository->update($attribute, ['name' => 'New']);

        $this->assertSame('New', $updated->name);
    }

    public function test_delete(): void
    {
        $attribute = Attribute::factory()->create();

        $this->repository->delete($attribute);

        $this->assertSoftDeleted('ecommerce_attributes', ['id' => $attribute->id]);
    }

    public function test_get_by_group(): void
    {
        $group = AttributeGroup::factory()->create();
        Attribute::factory()->count(3)->forGroup($group)->create();
        Attribute::factory()->create(); // different group

        $attributes = $this->repository->getByGroup($group->id);

        $this->assertCount(3, $attributes);
    }

    public function test_get_filterable(): void
    {
        Attribute::factory()->filterable()->create();
        Attribute::factory()->create(['is_filterable' => false]);

        $filterable = $this->repository->getFilterable();

        $this->assertCount(1, $filterable);
    }
}
