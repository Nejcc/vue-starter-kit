<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use LaravelPlus\Ecommerce\Models\Category;
use LaravelPlus\Ecommerce\Models\Product;
use LaravelPlus\Ecommerce\Tests\TestCase;

final class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_be_created_with_factory(): void
    {
        $category = Category::factory()->create();

        $this->assertDatabaseHas('ecommerce_categories', ['id' => $category->id]);
    }

    public function test_it_casts_boolean_fields(): void
    {
        $category = Category::factory()->create(['is_active' => true]);

        $this->assertTrue($category->is_active);
    }

    public function test_it_belongs_to_parent(): void
    {
        $parent = Category::factory()->create();
        $child = Category::factory()->create(['parent_id' => $parent->id]);

        $this->assertTrue($child->parent->is($parent));
    }

    public function test_it_has_many_children(): void
    {
        $parent = Category::factory()->create();
        Category::factory()->count(3)->create(['parent_id' => $parent->id]);

        $this->assertCount(3, $parent->children);
    }

    public function test_it_belongs_to_many_products(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create();

        $category->products()->attach($product);

        $this->assertCount(1, $category->products);
        $this->assertTrue($category->products->contains($product));
    }

    public function test_it_auto_generates_slug(): void
    {
        $category = Category::factory()->create(['name' => 'Electronics', 'slug' => null]);

        $this->assertSame('electronics', $category->slug);
    }

    public function test_it_generates_unique_slug(): void
    {
        Category::factory()->create(['slug' => 'electronics']);
        $category = Category::factory()->create(['name' => 'Electronics', 'slug' => null]);

        $this->assertSame('electronics-1', $category->slug);
    }

    public function test_route_key_name_is_slug(): void
    {
        $category = new Category;

        $this->assertSame('slug', $category->getRouteKeyName());
    }

    public function test_get_ancestors_returns_parent_chain(): void
    {
        $root = Category::factory()->create(['name' => 'Root']);
        $middle = Category::factory()->create(['name' => 'Middle', 'parent_id' => $root->id]);
        $leaf = Category::factory()->create(['name' => 'Leaf', 'parent_id' => $middle->id]);

        $ancestors = $leaf->getAncestors();

        $this->assertCount(2, $ancestors);
        $this->assertTrue($ancestors->first()->is($root));
        $this->assertTrue($ancestors->last()->is($middle));
    }

    public function test_get_ancestors_returns_empty_for_root(): void
    {
        $root = Category::factory()->rootCategory()->create();

        $this->assertCount(0, $root->getAncestors());
    }

    public function test_get_descendants_returns_all_children(): void
    {
        $root = Category::factory()->create();
        $child = Category::factory()->create(['parent_id' => $root->id]);
        $grandchild = Category::factory()->create(['parent_id' => $child->id]);

        $descendants = $root->getDescendants();

        $this->assertCount(2, $descendants);
        $this->assertTrue($descendants->contains($child));
        $this->assertTrue($descendants->contains($grandchild));
    }

    public function test_get_breadcrumb_includes_self(): void
    {
        $root = Category::factory()->create();
        $child = Category::factory()->create(['parent_id' => $root->id]);

        $breadcrumb = $child->getBreadcrumb();

        $this->assertCount(2, $breadcrumb);
        $this->assertTrue($breadcrumb->first()->is($root));
        $this->assertTrue($breadcrumb->last()->is($child));
    }

    public function test_is_root_returns_true_for_root_category(): void
    {
        $root = Category::factory()->rootCategory()->create();

        $this->assertTrue($root->isRoot());
    }

    public function test_is_root_returns_false_for_child_category(): void
    {
        $parent = Category::factory()->create();
        $child = Category::factory()->create(['parent_id' => $parent->id]);

        $this->assertFalse($child->isRoot());
    }

    public function test_is_leaf_returns_true_when_no_children(): void
    {
        $category = Category::factory()->create();

        $this->assertTrue($category->isLeaf());
    }

    public function test_is_leaf_returns_false_when_has_children(): void
    {
        $parent = Category::factory()->create();
        Category::factory()->create(['parent_id' => $parent->id]);

        $this->assertFalse($parent->isLeaf());
    }

    public function test_scope_active(): void
    {
        Category::factory()->active()->create();
        Category::factory()->inactive()->create();

        $this->assertCount(1, Category::active()->get());
    }

    public function test_scope_root(): void
    {
        $root = Category::factory()->rootCategory()->create();
        Category::factory()->create(['parent_id' => $root->id]);

        $this->assertCount(1, Category::root()->get());
    }

    public function test_scope_ordered(): void
    {
        Category::factory()->create(['sort_order' => 3, 'name' => 'C']);
        Category::factory()->create(['sort_order' => 1, 'name' => 'A']);
        Category::factory()->create(['sort_order' => 2, 'name' => 'B']);

        $ordered = Category::ordered()->get();

        $this->assertSame('A', $ordered->first()->name);
        $this->assertSame('C', $ordered->last()->name);
    }

    public function test_soft_deletes_work(): void
    {
        $category = Category::factory()->create();
        $category->delete();

        $this->assertSoftDeleted('ecommerce_categories', ['id' => $category->id]);
        $this->assertCount(0, Category::all());
        $this->assertCount(1, Category::withTrashed()->get());
    }
}
