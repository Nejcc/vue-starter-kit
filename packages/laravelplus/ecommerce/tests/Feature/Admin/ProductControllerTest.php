<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use LaravelPlus\Ecommerce\Models\Category;
use LaravelPlus\Ecommerce\Models\Product;
use LaravelPlus\Ecommerce\Tests\TestCase;
use LaravelPlus\Ecommerce\Tests\User;

final class ProductControllerTest extends TestCase
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
        $response = $this->get('/admin/ecommerce/products');

        $response->assertRedirect('/login');
    }

    public function test_index_displays_products(): void
    {
        Product::factory()->count(3)->create();

        $response = $this->actingAs($this->user)
            ->get('/admin/ecommerce/products');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('admin/ecommerce/Products')
            ->has('products.data', 3)
        );
    }

    public function test_index_with_search(): void
    {
        Product::factory()->create(['name' => 'Blue Shirt']);
        Product::factory()->create(['name' => 'Red Pants']);

        $response = $this->actingAs($this->user)
            ->get('/admin/ecommerce/products?search=Blue');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('admin/ecommerce/Products')
            ->has('products.data', 1)
        );
    }

    public function test_index_with_category_filter(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create();
        $product->categories()->attach($category);

        Product::factory()->create();

        $response = $this->actingAs($this->user)
            ->get("/admin/ecommerce/products?category={$category->id}");

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('admin/ecommerce/Products')
            ->has('products.data', 1)
        );
    }

    public function test_create_displays_form(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/admin/ecommerce/products/create');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('admin/ecommerce/Products/Create')
            ->has('categories')
        );
    }

    public function test_store_creates_product(): void
    {
        $response = $this->actingAs($this->user)
            ->post('/admin/ecommerce/products', [
                'name' => 'New Product',
                'price' => 2999,
            ]);

        $response->assertRedirect(route('admin.ecommerce.products.index'));
        $this->assertDatabaseHas('ecommerce_products', ['name' => 'New Product']);
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user)
            ->post('/admin/ecommerce/products', []);

        $response->assertSessionHasErrors(['name', 'price']);
    }

    public function test_store_with_categories(): void
    {
        $categories = Category::factory()->count(2)->create();

        $response = $this->actingAs($this->user)
            ->post('/admin/ecommerce/products', [
                'name' => 'Categorized Product',
                'price' => 1000,
                'category_ids' => $categories->pluck('id')->toArray(),
            ]);

        $response->assertRedirect();
        $product = Product::query()->where('name', 'Categorized Product')->first();
        $this->assertCount(2, $product->categories);
    }

    public function test_edit_displays_form(): void
    {
        $product = Product::factory()->create();

        $response = $this->actingAs($this->user)
            ->get("/admin/ecommerce/products/{$product->slug}/edit");

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('admin/ecommerce/Products/Edit')
            ->has('product')
            ->has('categories')
        );
    }

    public function test_update_modifies_product(): void
    {
        $product = Product::factory()->create(['name' => 'Old Name']);

        $response = $this->actingAs($this->user)
            ->put("/admin/ecommerce/products/{$product->slug}", [
                'name' => 'Updated Name',
                'price' => $product->price,
            ]);

        $response->assertRedirect(route('admin.ecommerce.products.index'));
        $this->assertDatabaseHas('ecommerce_products', ['id' => $product->id, 'name' => 'Updated Name']);
    }

    public function test_update_validates_required_fields(): void
    {
        $product = Product::factory()->create();

        $response = $this->actingAs($this->user)
            ->put("/admin/ecommerce/products/{$product->slug}", []);

        $response->assertSessionHasErrors(['name', 'price']);
    }

    public function test_destroy_deletes_product(): void
    {
        $product = Product::factory()->create();

        $response = $this->actingAs($this->user)
            ->delete("/admin/ecommerce/products/{$product->slug}");

        $response->assertRedirect(route('admin.ecommerce.products.index'));
        $this->assertSoftDeleted('ecommerce_products', ['id' => $product->id]);
    }
}
