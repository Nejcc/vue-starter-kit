<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use LaravelPlus\Ecommerce\Models\Category;
use LaravelPlus\Ecommerce\Tests\TestCase;
use LaravelPlus\Ecommerce\Tests\User;

final class CategoryControllerTest extends TestCase
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
        $response = $this->get('/admin/ecommerce/categories');

        $response->assertRedirect('/login');
    }

    public function test_index_displays_categories(): void
    {
        Category::factory()->count(3)->create();

        $response = $this->actingAs($this->user)
            ->get('/admin/ecommerce/categories');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('admin/ecommerce/Categories')
            ->has('categories.data', 3)
        );
    }

    public function test_index_with_search(): void
    {
        Category::factory()->create(['name' => 'Electronics']);
        Category::factory()->create(['name' => 'Clothing']);

        $response = $this->actingAs($this->user)
            ->get('/admin/ecommerce/categories?search=Elect');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('admin/ecommerce/Categories')
            ->has('categories.data', 1)
        );
    }

    public function test_tree_displays_category_tree(): void
    {
        $root = Category::factory()->rootCategory()->create();
        Category::factory()->create(['parent_id' => $root->id]);

        $response = $this->actingAs($this->user)
            ->get('/admin/ecommerce/categories/tree');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('admin/ecommerce/Categories/Tree')
            ->has('tree')
        );
    }

    public function test_create_displays_form(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/admin/ecommerce/categories/create');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('admin/ecommerce/Categories/Create')
            ->has('parentCategories')
        );
    }

    public function test_store_creates_category(): void
    {
        $response = $this->actingAs($this->user)
            ->post('/admin/ecommerce/categories', [
                'name' => 'New Category',
                'is_active' => true,
            ]);

        $response->assertRedirect(route('admin.ecommerce.categories.index'));
        $this->assertDatabaseHas('ecommerce_categories', ['name' => 'New Category']);
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user)
            ->post('/admin/ecommerce/categories', []);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_store_with_parent(): void
    {
        $parent = Category::factory()->create();

        $response = $this->actingAs($this->user)
            ->post('/admin/ecommerce/categories', [
                'name' => 'Child Category',
                'parent_id' => $parent->id,
            ]);

        $response->assertRedirect();
        $child = Category::query()->where('name', 'Child Category')->first();
        $this->assertSame($parent->id, $child->parent_id);
    }

    public function test_edit_displays_form(): void
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->user)
            ->get("/admin/ecommerce/categories/{$category->slug}/edit");

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('admin/ecommerce/Categories/Edit')
            ->has('category')
            ->has('parentCategories')
        );
    }

    public function test_update_modifies_category(): void
    {
        $category = Category::factory()->create(['name' => 'Old Name']);

        $response = $this->actingAs($this->user)
            ->put("/admin/ecommerce/categories/{$category->slug}", [
                'name' => 'Updated Name',
            ]);

        $response->assertRedirect(route('admin.ecommerce.categories.index'));
        $this->assertDatabaseHas('ecommerce_categories', ['id' => $category->id, 'name' => 'Updated Name']);
    }

    public function test_update_validates_required_fields(): void
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->user)
            ->put("/admin/ecommerce/categories/{$category->slug}", []);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_destroy_deletes_category(): void
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->user)
            ->delete("/admin/ecommerce/categories/{$category->slug}");

        $response->assertRedirect(route('admin.ecommerce.categories.index'));
        $this->assertSoftDeleted('ecommerce_categories', ['id' => $category->id]);
    }

    public function test_reorder_categories(): void
    {
        $cat1 = Category::factory()->create(['sort_order' => 0]);
        $cat2 = Category::factory()->create(['sort_order' => 1]);

        $response = $this->actingAs($this->user)
            ->post('/admin/ecommerce/categories/reorder', [
                'order' => [$cat1->id => 2, $cat2->id => 1],
            ]);

        $response->assertRedirect(route('admin.ecommerce.categories.index'));
        $this->assertSame(2, $cat1->fresh()->sort_order);
        $this->assertSame(1, $cat2->fresh()->sort_order);
    }
}
