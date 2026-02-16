<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Tests\Unit\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use LaravelPlus\Ecommerce\Models\Attribute;
use LaravelPlus\Ecommerce\Models\AttributeGroup;
use LaravelPlus\Ecommerce\Models\Product;
use LaravelPlus\Ecommerce\Services\AttributeService;
use LaravelPlus\Ecommerce\Tests\TestCase;

final class AttributeServiceTest extends TestCase
{
    use RefreshDatabase;

    private AttributeService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = $this->app->make(AttributeService::class);
    }

    public function test_create_attribute(): void
    {
        $group = AttributeGroup::factory()->create();

        $attribute = $this->service->create([
            'attribute_group_id' => $group->id,
            'name' => 'Color',
            'type' => 'text',
        ]);

        $this->assertInstanceOf(Attribute::class, $attribute);
        $this->assertSame('Color', $attribute->name);
    }

    public function test_update_attribute(): void
    {
        $attribute = Attribute::factory()->create(['name' => 'Old']);

        $updated = $this->service->update($attribute, ['name' => 'New']);

        $this->assertSame('New', $updated->name);
    }

    public function test_delete_attribute(): void
    {
        $attribute = Attribute::factory()->create();

        $result = $this->service->delete($attribute);

        $this->assertTrue($result);
        $this->assertSoftDeleted('ecommerce_attributes', ['id' => $attribute->id]);
    }

    public function test_get_by_group(): void
    {
        $group = AttributeGroup::factory()->create();
        Attribute::factory()->count(3)->forGroup($group)->create();

        $attributes = $this->service->getByGroup($group->id);

        $this->assertCount(3, $attributes);
    }

    public function test_get_filterable(): void
    {
        Attribute::factory()->filterable()->create();
        Attribute::factory()->create(['is_filterable' => false]);

        $filterable = $this->service->getFilterable();

        $this->assertCount(1, $filterable);
    }

    public function test_sync_product_attributes(): void
    {
        $product = Product::factory()->create();
        $attr1 = Attribute::factory()->create();
        $attr2 = Attribute::factory()->create();

        $this->service->syncProductAttributes($product, [
            $attr1->id => 'Red',
            $attr2->id => 'Large',
        ]);

        $product->refresh();
        $this->assertCount(2, $product->attributes);
        $this->assertSame('Red', $product->attributes->where('id', $attr1->id)->first()->pivot->value);
        $this->assertSame('Large', $product->attributes->where('id', $attr2->id)->first()->pivot->value);
    }

    public function test_sync_product_attributes_replaces_existing(): void
    {
        $product = Product::factory()->create();
        $attr1 = Attribute::factory()->create();
        $attr2 = Attribute::factory()->create();

        $product->attributes()->attach($attr1, ['value' => 'Old']);
        $this->service->syncProductAttributes($product, [$attr2->id => 'New']);

        $product->refresh();
        $this->assertCount(1, $product->attributes);
        $this->assertTrue($product->attributes->contains($attr2));
    }
}
