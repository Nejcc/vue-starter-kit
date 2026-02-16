<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use LaravelPlus\Ecommerce\Models\Tag;
use LaravelPlus\Ecommerce\Tests\TestCase;
use LaravelPlus\Ecommerce\Tests\User;

final class TagControllerTest extends TestCase
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
        $response = $this->get('/admin/ecommerce/tags');

        $response->assertRedirect('/login');
    }

    public function test_index_displays_tags(): void
    {
        Tag::factory()->count(3)->create();

        $response = $this->actingAs($this->user)
            ->get('/admin/ecommerce/tags');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('admin/ecommerce/Tags')
            ->has('tags.data', 3)
        );
    }

    public function test_index_with_search(): void
    {
        Tag::factory()->create(['name' => 'Summer Sale']);
        Tag::factory()->create(['name' => 'Winter Collection']);

        $response = $this->actingAs($this->user)
            ->get('/admin/ecommerce/tags?search=Summer');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('admin/ecommerce/Tags')
            ->has('tags.data', 1)
        );
    }

    public function test_create_displays_form(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/admin/ecommerce/tags/create');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('admin/ecommerce/Tags/Create')
        );
    }

    public function test_store_creates_tag(): void
    {
        $response = $this->actingAs($this->user)
            ->post('/admin/ecommerce/tags', [
                'name' => 'New Tag',
            ]);

        $response->assertRedirect(route('admin.ecommerce.tags.index'));
        $this->assertDatabaseHas('ecommerce_tags', ['name' => 'New Tag']);
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user)
            ->post('/admin/ecommerce/tags', []);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_store_with_all_fields(): void
    {
        $response = $this->actingAs($this->user)
            ->post('/admin/ecommerce/tags', [
                'name' => 'Sale Tag',
                'slug' => 'sale-tag',
                'type' => 'product',
                'sort_order' => 5,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('ecommerce_tags', [
            'name' => 'Sale Tag',
            'slug' => 'sale-tag',
            'type' => 'product',
            'sort_order' => 5,
        ]);
    }

    public function test_edit_displays_form(): void
    {
        $tag = Tag::factory()->create();

        $response = $this->actingAs($this->user)
            ->get("/admin/ecommerce/tags/{$tag->slug}/edit");

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('admin/ecommerce/Tags/Edit')
            ->has('tag')
        );
    }

    public function test_update_modifies_tag(): void
    {
        $tag = Tag::factory()->create(['name' => 'Old Name']);

        $response = $this->actingAs($this->user)
            ->put("/admin/ecommerce/tags/{$tag->slug}", [
                'name' => 'Updated Name',
            ]);

        $response->assertRedirect(route('admin.ecommerce.tags.index'));
        $this->assertDatabaseHas('ecommerce_tags', ['id' => $tag->id, 'name' => 'Updated Name']);
    }

    public function test_update_validates_required_fields(): void
    {
        $tag = Tag::factory()->create();

        $response = $this->actingAs($this->user)
            ->put("/admin/ecommerce/tags/{$tag->slug}", []);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_destroy_deletes_tag(): void
    {
        $tag = Tag::factory()->create();

        $response = $this->actingAs($this->user)
            ->delete("/admin/ecommerce/tags/{$tag->slug}");

        $response->assertRedirect(route('admin.ecommerce.tags.index'));
        $this->assertDatabaseMissing('ecommerce_tags', ['id' => $tag->id]);
    }
}
