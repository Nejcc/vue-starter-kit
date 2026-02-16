<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Tests\Unit\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use LaravelPlus\Ecommerce\Models\Product;
use LaravelPlus\Ecommerce\Models\Tag;
use LaravelPlus\Ecommerce\Services\TagService;
use LaravelPlus\Ecommerce\Tests\TestCase;

final class TagServiceTest extends TestCase
{
    use RefreshDatabase;

    private TagService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = $this->app->make(TagService::class);
    }

    public function test_list_returns_paginated_tags(): void
    {
        Tag::factory()->count(5)->create();

        $result = $this->service->list(3);

        $this->assertCount(3, $result->items());
        $this->assertSame(5, $result->total());
    }

    public function test_list_with_search_filters_results(): void
    {
        Tag::factory()->create(['name' => 'Summer Sale']);
        Tag::factory()->create(['name' => 'Winter Collection']);

        $result = $this->service->list(15, 'Summer');

        $this->assertCount(1, $result->items());
        $this->assertSame('Summer Sale', $result->items()[0]->name);
    }

    public function test_create_tag(): void
    {
        $tag = $this->service->create([
            'name' => 'New Tag',
            'type' => 'product',
        ]);

        $this->assertInstanceOf(Tag::class, $tag);
        $this->assertSame('New Tag', $tag->name);
        $this->assertSame('product', $tag->type);
    }

    public function test_update_tag(): void
    {
        $tag = Tag::factory()->create(['name' => 'Old Name']);

        $updated = $this->service->update($tag, ['name' => 'New Name']);

        $this->assertSame('New Name', $updated->name);
    }

    public function test_delete_tag(): void
    {
        $tag = Tag::factory()->create();

        $result = $this->service->delete($tag);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('ecommerce_tags', ['id' => $tag->id]);
    }

    public function test_find_by_slug(): void
    {
        $tag = Tag::factory()->create(['slug' => 'test-slug']);

        $found = $this->service->findBySlug('test-slug');

        $this->assertTrue($found->is($tag));
    }

    public function test_find_by_slug_returns_null_when_not_found(): void
    {
        $this->assertNull($this->service->findBySlug('nonexistent'));
    }

    public function test_get_all(): void
    {
        Tag::factory()->count(3)->create();

        $tags = $this->service->getAll();

        $this->assertCount(3, $tags);
    }

    public function test_get_by_type(): void
    {
        Tag::factory()->create(['type' => 'product']);
        Tag::factory()->create(['type' => 'blog']);

        $tags = $this->service->getByType('product');

        $this->assertCount(1, $tags);
    }

    public function test_sync_product_tags(): void
    {
        $product = Product::factory()->create();
        $tags = Tag::factory()->count(3)->create();

        $this->service->syncProductTags($product, $tags->pluck('id')->toArray());

        $this->assertCount(3, $product->tags);
    }

    public function test_sync_product_tags_replaces_existing(): void
    {
        $product = Product::factory()->create();
        $oldTags = Tag::factory()->count(2)->create();
        $newTag = Tag::factory()->create();

        $product->tags()->attach($oldTags);
        $this->service->syncProductTags($product, [$newTag->id]);

        $product->refresh();
        $this->assertCount(1, $product->tags);
        $this->assertTrue($product->tags->contains($newTag));
    }
}
