<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use LaravelPlus\Ecommerce\Models\Attribute;
use LaravelPlus\Ecommerce\Models\AttributeGroup;
use LaravelPlus\Ecommerce\Tests\TestCase;
use LaravelPlus\Ecommerce\Tests\User;

final class AttributeControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private AttributeGroup $group;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->group = AttributeGroup::factory()->create();
    }

    public function test_store_creates_attribute(): void
    {
        $response = $this->actingAs($this->user)
            ->post("/admin/ecommerce/attributes/{$this->group->slug}/items", [
                'name' => 'Color',
                'type' => 'text',
            ]);

        $response->assertRedirect(route('admin.ecommerce.attributes.edit', $this->group));
        $this->assertDatabaseHas('ecommerce_attributes', [
            'name' => 'Color',
            'attribute_group_id' => $this->group->id,
        ]);
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user)
            ->post("/admin/ecommerce/attributes/{$this->group->slug}/items", []);

        $response->assertSessionHasErrors(['name', 'type']);
    }

    public function test_store_with_select_type_requires_values(): void
    {
        $response = $this->actingAs($this->user)
            ->post("/admin/ecommerce/attributes/{$this->group->slug}/items", [
                'name' => 'Size',
                'type' => 'select',
            ]);

        $response->assertSessionHasErrors(['values']);
    }

    public function test_store_with_select_type_and_values(): void
    {
        $response = $this->actingAs($this->user)
            ->post("/admin/ecommerce/attributes/{$this->group->slug}/items", [
                'name' => 'Size',
                'type' => 'select',
                'values' => ['Small', 'Medium', 'Large'],
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('ecommerce_attributes', ['name' => 'Size', 'type' => 'select']);
    }

    public function test_update_modifies_attribute(): void
    {
        $attribute = Attribute::factory()->forGroup($this->group)->create(['name' => 'Old']);

        $response = $this->actingAs($this->user)
            ->put("/admin/ecommerce/attributes/{$this->group->slug}/items/{$attribute->slug}", [
                'name' => 'Updated',
                'type' => 'text',
            ]);

        $response->assertRedirect(route('admin.ecommerce.attributes.edit', $this->group));
        $this->assertDatabaseHas('ecommerce_attributes', ['id' => $attribute->id, 'name' => 'Updated']);
    }

    public function test_destroy_deletes_attribute(): void
    {
        $attribute = Attribute::factory()->forGroup($this->group)->create();

        $response = $this->actingAs($this->user)
            ->delete("/admin/ecommerce/attributes/{$this->group->slug}/items/{$attribute->slug}");

        $response->assertRedirect(route('admin.ecommerce.attributes.edit', $this->group));
        $this->assertSoftDeleted('ecommerce_attributes', ['id' => $attribute->id]);
    }
}
