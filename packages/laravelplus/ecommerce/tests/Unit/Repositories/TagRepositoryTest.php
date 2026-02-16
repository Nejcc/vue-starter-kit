<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Tests\Unit\Repositories;

use Illuminate\Foundation\Testing\RefreshDatabase;
use LaravelPlus\Ecommerce\Models\Tag;
use LaravelPlus\Ecommerce\Repositories\TagRepository;
use LaravelPlus\Ecommerce\Tests\TestCase;

final class TagRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private TagRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new TagRepository;
    }

    public function test_find(): void
    {
        $tag = Tag::factory()->create();

        $found = $this->repository->find($tag->id);

        $this->assertTrue($found->is($tag));
    }

    public function test_find_returns_null_when_not_found(): void
    {
        $this->assertNull($this->repository->find(999));
    }

    public function test_find_or_fail(): void
    {
        $tag = Tag::factory()->create();

        $found = $this->repository->findOrFail($tag->id);

        $this->assertTrue($found->is($tag));
    }

    public function test_find_or_fail_throws_exception(): void
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->repository->findOrFail(999);
    }

    public function test_find_by_slug(): void
    {
        $tag = Tag::factory()->create(['slug' => 'test-tag']);

        $found = $this->repository->findBySlug('test-tag');

        $this->assertTrue($found->is($tag));
    }

    public function test_create(): void
    {
        $tag = $this->repository->create([
            'name' => 'Test Tag',
        ]);

        $this->assertDatabaseHas('ecommerce_tags', ['name' => 'Test Tag']);
    }

    public function test_update(): void
    {
        $tag = Tag::factory()->create(['name' => 'Old']);

        $updated = $this->repository->update($tag, ['name' => 'New']);

        $this->assertSame('New', $updated->name);
    }

    public function test_delete(): void
    {
        $tag = Tag::factory()->create();

        $this->repository->delete($tag);

        $this->assertDatabaseMissing('ecommerce_tags', ['id' => $tag->id]);
    }

    public function test_paginate(): void
    {
        Tag::factory()->count(5)->create();

        $result = $this->repository->paginate(3);

        $this->assertCount(3, $result->items());
        $this->assertSame(5, $result->total());
    }

    public function test_search(): void
    {
        Tag::factory()->create(['name' => 'Sale']);
        Tag::factory()->create(['name' => 'New Arrival']);

        $result = $this->repository->search('Sale');

        $this->assertCount(1, $result->items());
    }

    public function test_get_by_type(): void
    {
        Tag::factory()->create(['type' => 'product']);
        Tag::factory()->create(['type' => 'blog']);
        Tag::factory()->create(['type' => 'product']);

        $tags = $this->repository->getByType('product');

        $this->assertCount(2, $tags);
    }
}
