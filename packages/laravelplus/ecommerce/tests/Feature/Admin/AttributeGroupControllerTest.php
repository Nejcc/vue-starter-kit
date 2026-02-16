<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use LaravelPlus\Ecommerce\Models\AttributeGroup;
use LaravelPlus\Ecommerce\Tests\TestCase;
use LaravelPlus\Ecommerce\Tests\User;

final class AttributeGroupControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_index_requires_authentication(): void
    {
        $response = $this->get('/admin/ecommerce/attributes');

        $response->assertRedirect('/login');
    }

    public function test_index_displays_attribute_groups(): void
    {
        AttributeGroup::factory()->count(3)->create();

        $response = $this->actingAs($this->user)
            ->get('/admin/ecommerce/attributes');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('admin/ecommerce/Attributes')
            ->has('attributeGroups.data', 3)
        );
    }

    public function test_index_with_search(): void
    {
        AttributeGroup::factory()->create(['name' => 'Physical']);
        AttributeGroup::factory()->create(['name' => 'Colors']);

        $response = $this->actingAs($this->user)
            ->get('/admin/ecommerce/attributes?search=Physical');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('admin/ecommerce/Attributes')
            ->has('attributeGroups.data', 1)
        );
    }

    public function test_create_displays_form(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/admin/ecommerce/attributes/create');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('admin/ecommerce/Attributes/Create')
            ->has('attributeTypes')
        );
    }

    public function test_store_creates_attribute_group(): void
    {
        $response = $this->actingAs($this->user)
            ->post('/admin/ecommerce/attributes', [
                'name' => 'New Group',
            ]);

        $response->assertRedirect(route('admin.ecommerce.attributes.index'));
        $this->assertDatabaseHas('ecommerce_attribute_groups', ['name' => 'New Group']);
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user)
            ->post('/admin/ecommerce/attributes', []);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_edit_displays_form(): void
    {
        $group = AttributeGroup::factory()->create();

        $response = $this->actingAs($this->user)
            ->get("/admin/ecommerce/attributes/{$group->slug}/edit");

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('admin/ecommerce/Attributes/Edit')
            ->has('attributeGroup')
            ->has('attributeTypes')
        );
    }

    public function test_update_modifies_attribute_group(): void
    {
        $group = AttributeGroup::factory()->create(['name' => 'Old Name']);

        $response = $this->actingAs($this->user)
            ->put("/admin/ecommerce/attributes/{$group->slug}", [
                'name' => 'Updated Name',
            ]);

        $response->assertRedirect(route('admin.ecommerce.attributes.index'));
        $this->assertDatabaseHas('ecommerce_attribute_groups', ['id' => $group->id, 'name' => 'Updated Name']);
    }

    public function test_destroy_deletes_attribute_group(): void
    {
        $group = AttributeGroup::factory()->create();

        $response = $this->actingAs($this->user)
            ->delete("/admin/ecommerce/attributes/{$group->slug}");

        $response->assertRedirect(route('admin.ecommerce.attributes.index'));
        $this->assertSoftDeleted('ecommerce_attribute_groups', ['id' => $group->id]);
    }
}
