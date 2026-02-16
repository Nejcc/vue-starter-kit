<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Tests\Unit\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use LaravelPlus\Ecommerce\Models\Category;
use LaravelPlus\Ecommerce\Services\CategoryService;
use LaravelPlus\Ecommerce\Tests\TestCase;

final class CategoryServiceTest extends TestCase
{
    use RefreshDatabase;

    private CategoryService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = $this->app->make(CategoryService::class);
    }

    public function test_list_returns_paginated_categories(): void
    {
        Category::factory()->count(5)->create();

        $result = $this->service->list(3);

        $this->assertCount(3, $result->items());
        $this->assertSame(5, $result->total());
    }

    public function test_list_with_search_filters_results(): void
    {
        Category::factory()->create(['name' => 'Electronics']);
        Category::factory()->create(['name' => 'Clothing']);

        $result = $this->service->list(15, 'Elect');

        $this->assertCount(1, $result->items());
        $this->assertSame('Electronics', $result->items()[0]->name);
    }

    public function test_create_category(): void
    {
        $category = $this->service->create([
            'name' => 'New Category',
            'is_active' => true,
        ]);

        $this->assertInstanceOf(Category::class, $category);
        $this->assertSame('New Category', $category->name);
        $this->assertDatabaseHas('ecommerce_categories', ['name' => 'New Category']);
    }

    public function test_update_category(): void
    {
        $category = Category::factory()->create(['name' => 'Old Name']);

        $updated = $this->service->update($category, ['name' => 'New Name']);

        $this->assertSame('New Name', $updated->name);
    }

    public function test_delete_category(): void
    {
        $category = Category::factory()->create();

        $result = $this->service->delete($category);

        $this->assertTrue($result);
        $this->assertSoftDeleted('ecommerce_categories', ['id' => $category->id]);
    }

    public function test_get_root_categories(): void
    {
        $root = Category::factory()->rootCategory()->create();
        Category::factory()->create(['parent_id' => $root->id]);

        $roots = $this->service->getRootCategories();

        $this->assertCount(1, $roots);
        $this->assertTrue($roots->first()->is($root));
    }

    public function test_get_tree(): void
    {
        $root = Category::factory()->rootCategory()->create();
        Category::factory()->create(['parent_id' => $root->id]);

        $tree = $this->service->getTree();

        $this->assertCount(1, $tree);
        $this->assertCount(1, $tree->first()->children);
    }

    public function test_reorder(): void
    {
        $cat1 = Category::factory()->create(['sort_order' => 0]);
        $cat2 = Category::factory()->create(['sort_order' => 1]);

        $this->service->reorder([$cat1->id => 2, $cat2->id => 1]);

        $this->assertSame(2, $cat1->fresh()->sort_order);
        $this->assertSame(1, $cat2->fresh()->sort_order);
    }

    public function test_move(): void
    {
        $parent = Category::factory()->create();
        $child = Category::factory()->rootCategory()->create();

        $moved = $this->service->move($child, $parent->id);

        $this->assertSame($parent->id, $moved->parent_id);
    }

    public function test_move_to_root(): void
    {
        $parent = Category::factory()->create();
        $child = Category::factory()->create(['parent_id' => $parent->id]);

        $moved = $this->service->move($child, null);

        $this->assertNull($moved->parent_id);
    }

    public function test_find_by_slug(): void
    {
        $category = Category::factory()->create(['slug' => 'test-cat']);

        $found = $this->service->findBySlug('test-cat');

        $this->assertTrue($found->is($category));
    }

    public function test_find_by_slug_returns_null_when_not_found(): void
    {
        $this->assertNull($this->service->findBySlug('nonexistent'));
    }
}
