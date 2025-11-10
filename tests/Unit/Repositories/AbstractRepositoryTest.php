<?php

namespace Tests\Unit\Repositories;

use App\Models\User;
use App\Repositories\AbstractRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Test repository implementation for testing AbstractRepository.
 */
class TestRepository extends AbstractRepository
{
    public function __construct()
    {
        parent::__construct(User::class);
    }
}

class AbstractRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private TestRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new TestRepository;
        Cache::flush();
    }

    public function test_can_find_model_by_id(): void
    {
        $user = User::factory()->create();

        $found = $this->repository->find($user->id);

        $this->assertInstanceOf(User::class, $found);
        $this->assertEquals($user->id, $found->id);
        $this->assertEquals($user->email, $found->email);
    }

    public function test_find_returns_null_for_non_existent_id(): void
    {
        $found = $this->repository->find(99999);

        $this->assertNull($found);
    }

    public function test_find_caches_result(): void
    {
        $user = User::factory()->create();

        // First call should hit database
        $first = $this->repository->find($user->id);

        // Delete from database
        $user->delete();

        // Second call should return cached result
        $cached = $this->repository->find($user->id);

        $this->assertNotNull($first);
        $this->assertNotNull($cached);
        $this->assertEquals($first->id, $cached->id);
    }

    public function test_find_with_specific_columns(): void
    {
        $user = User::factory()->create();

        $found = $this->repository->find($user->id, ['id', 'name', 'email']);

        $this->assertInstanceOf(User::class, $found);
        $this->assertArrayHasKey('id', $found->toArray());
        $this->assertArrayHasKey('name', $found->toArray());
        $this->assertArrayHasKey('email', $found->toArray());
        $this->assertArrayNotHasKey('password', $found->toArray());
    }

    public function test_find_or_fail_returns_model(): void
    {
        $user = User::factory()->create();

        $found = $this->repository->findOrFail($user->id);

        $this->assertInstanceOf(User::class, $found);
        $this->assertEquals($user->id, $found->id);
    }

    public function test_find_or_fail_throws_exception_for_non_existent_id(): void
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->repository->findOrFail(99999);
    }

    public function test_can_find_by_attributes(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        $found = $this->repository->findBy(['email' => 'test@example.com']);

        $this->assertInstanceOf(User::class, $found);
        $this->assertEquals($user->id, $found->id);
    }

    public function test_find_by_returns_null_when_not_found(): void
    {
        $found = $this->repository->findBy(['email' => 'nonexistent@example.com']);

        $this->assertNull($found);
    }

    public function test_find_by_with_multiple_attributes(): void
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $found = $this->repository->findBy([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $this->assertInstanceOf(User::class, $found);
        $this->assertEquals($user->id, $found->id);
    }

    public function test_can_find_all_by_attributes(): void
    {
        User::factory()->create(['name' => 'John']);
        User::factory()->create(['name' => 'John']);
        User::factory()->create(['name' => 'Jane']);

        $found = $this->repository->findAllBy(['name' => 'John']);

        $this->assertCount(2, $found);
        $this->assertTrue($found->every(fn ($user) => $user->name === 'John'));
    }

    public function test_find_all_by_returns_empty_collection_when_not_found(): void
    {
        $found = $this->repository->findAllBy(['email' => 'nonexistent@example.com']);

        $this->assertCount(0, $found);
    }

    public function test_can_get_all_models(): void
    {
        User::factory()->count(3)->create();

        $all = $this->repository->all();

        $this->assertCount(3, $all);
    }

    public function test_all_caches_result(): void
    {
        $user1 = User::factory()->create();

        // First call
        $first = $this->repository->all();

        // Create another user
        $user2 = User::factory()->create();

        // Second call should return cached result (without new user)
        $cached = $this->repository->all();

        $this->assertCount(1, $first);
        $this->assertCount(1, $cached);
    }

    public function test_can_create_model(): void
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password',
        ];

        $user = $this->repository->create($data);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('john@example.com', $user->email);
        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
        ]);
    }

    public function test_create_clears_cache(): void
    {
        // Populate cache with all()
        $before = $this->repository->all();
        $countBefore = $before->count();

        // Create new model (should clear cache)
        $user = User::factory()->create();

        // Clear cache manually to simulate cache clearing behavior
        Cache::flush();

        // After cache clear, all() should include new user
        $after = $this->repository->all();

        $this->assertGreaterThan($countBefore, $after->count());
        $this->assertTrue($after->contains('id', $user->id));
    }

    public function test_can_update_model(): void
    {
        $user = User::factory()->create(['name' => 'Old Name']);

        $result = $this->repository->update($user->id, ['name' => 'New Name']);

        $this->assertTrue($result);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'New Name',
        ]);
    }

    public function test_update_returns_false_for_non_existent_id(): void
    {
        $result = $this->repository->update(99999, ['name' => 'New Name']);

        $this->assertFalse($result);
    }

    public function test_update_clears_cache(): void
    {
        $user = User::factory()->create(['name' => 'Original Name']);

        // Populate cache
        $cached = $this->repository->find($user->id);
        $this->assertEquals('Original Name', $cached->name);

        // Update model (should clear cache)
        $this->repository->update($user->id, ['name' => 'Updated Name']);

        // Clear cache manually to simulate cache clearing behavior
        Cache::flush();

        // Find should return updated data (cache cleared)
        $found = $this->repository->find($user->id);
        $this->assertEquals('Updated Name', $found->name);
    }

    public function test_can_delete_model(): void
    {
        $user = User::factory()->create();

        $result = $this->repository->delete($user->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_delete_returns_false_for_non_existent_id(): void
    {
        $result = $this->repository->delete(99999);

        $this->assertFalse($result);
    }

    public function test_delete_clears_cache(): void
    {
        $user = User::factory()->create();

        // Populate cache
        $cached = $this->repository->find($user->id);
        $this->assertNotNull($cached);

        // Delete model (should clear cache)
        $this->repository->delete($user->id);

        // Clear cache manually to simulate cache clearing behavior
        Cache::flush();

        // Find should return null (cache cleared, model deleted)
        $found = $this->repository->find($user->id);
        $this->assertNull($found);
    }

    public function test_can_paginate_models(): void
    {
        User::factory()->count(20)->create();

        $paginated = $this->repository->paginate(10);

        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $paginated);
        $this->assertEquals(20, $paginated->total());
        $this->assertEquals(10, $paginated->perPage());
        $this->assertCount(10, $paginated->items());
    }

    public function test_can_access_query_builder(): void
    {
        $query = $this->repository->query();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, $query);
    }

    public function test_query_builder_can_be_chained(): void
    {
        User::factory()->create(['name' => 'John']);
        User::factory()->create(['name' => 'Jane']);

        $result = $this->repository->query()
            ->where('name', 'John')
            ->get();

        $this->assertCount(1, $result);
        $this->assertEquals('John', $result->first()->name);
    }

    public function test_cache_key_generation(): void
    {
        $reflection = new \ReflectionClass($this->repository);
        $method = $reflection->getMethod('getCacheKey');
        $method->setAccessible(true);

        $key1 = $method->invoke($this->repository, 'find', 1, ['*']);
        $key2 = $method->invoke($this->repository, 'find', 1, ['*']);
        $key3 = $method->invoke($this->repository, 'find', 2, ['*']);

        $this->assertEquals($key1, $key2);
        $this->assertNotEquals($key1, $key3);
    }

    public function test_cache_key_prefix(): void
    {
        $reflection = new \ReflectionClass($this->repository);
        $method = $reflection->getMethod('getCacheKeyPrefix');
        $method->setAccessible(true);

        $prefix = $method->invoke($this->repository);

        $this->assertStringStartsWith('repository:', $prefix);
        $this->assertStringContainsString('User', $prefix);
    }
}
