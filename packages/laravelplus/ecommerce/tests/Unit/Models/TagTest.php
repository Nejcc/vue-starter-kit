<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use LaravelPlus\Ecommerce\Models\Product;
use LaravelPlus\Ecommerce\Models\Tag;
use LaravelPlus\Ecommerce\Tests\TestCase;

final class TagTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_be_created_with_factory(): void
    {
        $tag = Tag::factory()->create();

        $this->assertDatabaseHas('ecommerce_tags', ['id' => $tag->id]);
    }

    public function test_it_casts_sort_order_to_integer(): void
    {
        $tag = Tag::factory()->create(['sort_order' => 5]);

        $this->assertIsInt($tag->sort_order);
        $this->assertSame(5, $tag->sort_order);
    }

    public function test_it_auto_generates_slug(): void
    {
        $tag = Tag::factory()->create(['name' => 'My Test Tag', 'slug' => null]);

        $this->assertSame('my-test-tag', $tag->slug);
    }

    public function test_it_generates_unique_slug(): void
    {
        Tag::factory()->create(['slug' => 'test-tag']);
        $tag = Tag::factory()->create(['name' => 'Test Tag', 'slug' => null]);

        $this->assertSame('test-tag-1', $tag->slug);
    }

    public function test_route_key_name_is_slug(): void
    {
        $tag = new Tag;

        $this->assertSame('slug', $tag->getRouteKeyName());
    }

    public function test_it_morphed_by_many_products(): void
    {
        $tag = Tag::factory()->create();
        $product = Product::factory()->create();

        $product->tags()->attach($tag);

        $this->assertCount(1, $tag->products);
        $this->assertTrue($tag->products->contains($product));
    }

    public function test_scope_ordered(): void
    {
        Tag::factory()->create(['sort_order' => 3, 'name' => 'Charlie']);
        Tag::factory()->create(['sort_order' => 1, 'name' => 'Alpha']);
        Tag::factory()->create(['sort_order' => 1, 'name' => 'Beta']);

        $tags = Tag::ordered()->get();

        $this->assertSame('Alpha', $tags[0]->name);
        $this->assertSame('Beta', $tags[1]->name);
        $this->assertSame('Charlie', $tags[2]->name);
    }

    public function test_scope_of_type(): void
    {
        Tag::factory()->create(['type' => 'product']);
        Tag::factory()->create(['type' => 'blog']);
        Tag::factory()->create(['type' => 'product']);

        $this->assertCount(2, Tag::ofType('product')->get());
        $this->assertCount(1, Tag::ofType('blog')->get());
    }

    public function test_factory_product_type_state(): void
    {
        $tag = Tag::factory()->productType()->create();

        $this->assertSame('product', $tag->type);
    }

    public function test_factory_sorted_state(): void
    {
        $tag = Tag::factory()->sorted(42)->create();

        $this->assertSame(42, $tag->sort_order);
    }
}
