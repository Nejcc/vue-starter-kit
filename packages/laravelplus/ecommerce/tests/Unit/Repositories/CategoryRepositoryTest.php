<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Tests\Unit\Repositories;

use Illuminate\Foundation\Testing\RefreshDatabase;
use LaravelPlus\Ecommerce\Models\Category;
use LaravelPlus\Ecommerce\Repositories\CategoryRepository;
use LaravelPlus\Ecommerce\Tests\TestCase;

final class CategoryRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private CategoryRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new CategoryRepository;
    }

    public function test_find(): void
    {
        $category = Category::factory()->create();

        $found = $this->repository->find($category->id);

        $this->assertTrue($found->is($category));
    }

    public function test_find_returns_null_when_not_found(): void
    {
        $this->assertNull($this->repository->find(999));
    }

    public function test_find_or_fail(): void
    {
        $category = Category::factory()->create();

        $found = $this->repository->findOrFail($category->id);

        $this->assertTrue($found->is($category));
    }

    public function test_find_or_fail_throws_exception(): void
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->repository->findOrFail(999);
    }

    public function test_find_by_slug(): void
    {
        $category = Category::factory()->create(['slug' => 'test-cat']);

        $found = $this->repository->findBySlug('test-cat');

        $this->assertTrue($found->is($category));
    }

    public function test_create(): void
    {
        $category = $this->repository->create([
            'name' => 'Test Category',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('ecommerce_categories', ['name' => 'Test Category']);
    }

    public function test_update(): void
    {
        $category = Category::factory()->create(['name' => 'Old']);

        $updated = $this->repository->update($category, ['name' => 'New']);

        $this->assertSame('New', $updated->name);
    }

    public function test_delete(): void
    {
        $category = Category::factory()->create();

        $this->repository->delete($category);

        $this->assertSoftDeleted('ecommerce_categories', ['id' => $category->id]);
    }

    public function test_paginate(): void
    {
        Category::factory()->count(5)->create();

        $result = $this->repository->paginate(3);

        $this->assertCount(3, $result->items());
        $this->assertSame(5, $result->total());
    }

    public function test_get_root_categories(): void
    {
        $root = Category::factory()->rootCategory()->create();
        Category::factory()->create(['parent_id' => $root->id]);

        $roots = $this->repository->getRootCategories();

        $this->assertCount(1, $roots);
        $this->assertTrue($roots->first()->is($root));
    }

    public function test_get_tree(): void
    {
        $root = Category::factory()->rootCategory()->create();
        Category::factory()->create(['parent_id' => $root->id]);

        $tree = $this->repository->getTree();

        $this->assertCount(1, $tree);
        $this->assertTrue($tree->first()->relationLoaded('children'));
    }

    public function test_reorder(): void
    {
        $cat1 = Category::factory()->create(['sort_order' => 0]);
        $cat2 = Category::factory()->create(['sort_order' => 1]);

        $this->repository->reorder([$cat1->id => 5, $cat2->id => 3]);

        $this->assertSame(5, $cat1->fresh()->sort_order);
        $this->assertSame(3, $cat2->fresh()->sort_order);
    }
}
