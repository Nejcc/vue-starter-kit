<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\RepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

/**
 * Abstract repository implementation providing base CRUD operations with caching.
 *
 * This repository implements the Repository Pattern, providing a clean abstraction
 * layer for database operations. It includes built-in caching for read operations
 * and automatic cache invalidation on write operations.
 *
 * @template T of Model
 *
 * @example
 * class UserRepository extends AbstractRepository
 * {
 *     public function __construct()
 *     {
 *         parent::__construct(User::class);
 *     }
 * }
 */
abstract class AbstractRepository implements RepositoryInterface
{
    /**
     * The model class name.
     *
     * @var class-string<T>
     */
    protected string $model;

    /**
     * Cache TTL in seconds.
     *
     * Defaults to 3600 seconds (1 hour). Override in child classes to customize.
     */
    protected int $cacheTtl = 3600;

    /**
     * Create a new repository instance.
     *
     * @param  class-string<T>  $model  The Eloquent model class name
     */
    public function __construct(string $model)
    {
        $this->model = $model;
    }

    /**
     * Find a model by its primary key.
     *
     * Results are cached for improved performance. Cache is automatically
     * invalidated when the model is updated or deleted.
     *
     * @param  mixed  $id  The primary key value
     * @param  array<string>  $columns  The columns to retrieve
     * @return T|null The model instance or null if not found
     *
     * @example
     * $user = $repository->find(1);
     * $user = $repository->find(1, ['id', 'name', 'email']);
     */
    public function find(mixed $id, array $columns = ['*']): ?Model
    {
        $cacheKey = $this->getCacheKey('find', $id, $columns);

        return Cache::remember($cacheKey, $this->cacheTtl, fn () => $this->query()->find($id, $columns));
    }

    /**
     * Find a model by its primary key or throw an exception.
     *
     * Unlike find(), this method throws a ModelNotFoundException if the model
     * is not found. This method does not use caching.
     *
     * @param  mixed  $id  The primary key value
     * @param  array<string>  $columns  The columns to retrieve
     * @return T The model instance
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If the model is not found
     *
     * @example
     * $user = $repository->findOrFail(1);
     */
    public function findOrFail(mixed $id, array $columns = ['*']): Model
    {
        return $this->query()->findOrFail($id, $columns);
    }

    /**
     * Find a model by the given attributes.
     *
     * Searches for a model matching all provided attributes. Multiple attributes
     * are combined with AND logic.
     *
     * @param  array<string, mixed>  $attributes  Key-value pairs to search for
     * @param  array<string>  $columns  The columns to retrieve
     * @return T|null The first matching model or null if not found
     *
     * @example
     * $user = $repository->findBy(['email' => 'user@example.com']);
     * $user = $repository->findBy(['name' => 'John', 'active' => true]);
     */
    public function findBy(array $attributes, array $columns = ['*']): ?Model
    {
        $query = $this->query();

        foreach ($attributes as $key => $value) {
            $query->where($key, $value);
        }

        return $query->first($columns);
    }

    /**
     * Find all models matching the given attributes.
     *
     * Returns a collection of all models matching the provided attributes.
     * Multiple attributes are combined with AND logic.
     *
     * @param  array<string, mixed>  $attributes  Key-value pairs to search for
     * @param  array<string>  $columns  The columns to retrieve
     * @return Collection<int, T> Collection of matching models
     *
     * @example
     * $users = $repository->findAllBy(['active' => true]);
     */
    public function findAllBy(array $attributes, array $columns = ['*']): Collection
    {
        $query = $this->query();

        foreach ($attributes as $key => $value) {
            $query->where($key, $value);
        }

        return $query->get($columns);
    }

    /**
     * Get all models.
     *
     * Retrieves all models from the database. Results are cached for improved
     * performance. Cache is automatically invalidated when models are created,
     * updated, or deleted.
     *
     * @param  array<string>  $columns  The columns to retrieve
     * @return Collection<int, T> Collection of all models
     *
     * @example
     * $allUsers = $repository->all();
     * $allUsers = $repository->all(['id', 'name']);
     */
    public function all(array $columns = ['*']): Collection
    {
        $cacheKey = $this->getCacheKey('all', $columns);

        return Cache::remember($cacheKey, $this->cacheTtl, fn () => $this->query()->get($columns));
    }

    /**
     * Create a new model instance.
     *
     * Creates a new model with the provided attributes and automatically
     * clears the repository cache to ensure fresh data on subsequent reads.
     *
     * @param  array<string, mixed>  $attributes  The model attributes
     * @return T The newly created model instance
     *
     * @example
     * $user = $repository->create([
     *     'name' => 'John Doe',
     *     'email' => 'john@example.com',
     * ]);
     */
    public function create(array $attributes): Model
    {
        $model = $this->query()->create($attributes);
        $this->clearCache();

        return $model;
    }

    /**
     * Update a model by its primary key.
     *
     * Updates the model with the provided attributes and automatically
     * clears the repository cache to ensure fresh data on subsequent reads.
     *
     * @param  mixed  $id  The primary key value
     * @param  array<string, mixed>  $attributes  The attributes to update
     * @return bool True if the update was successful, false otherwise
     *
     * @example
     * $success = $repository->update(1, ['name' => 'Updated Name']);
     */
    public function update(mixed $id, array $attributes): bool
    {
        $result = $this->query()->where('id', $id)->update($attributes);
        $this->clearCache();

        return $result > 0;
    }

    /**
     * Delete a model by its primary key.
     *
     * Deletes the model and automatically clears the repository cache
     * to ensure fresh data on subsequent reads.
     *
     * @param  mixed  $id  The primary key value
     * @return bool True if the deletion was successful, false otherwise
     *
     * @example
     * $success = $repository->delete(1);
     */
    public function delete(mixed $id): bool
    {
        $result = $this->query()->where('id', $id)->delete();
        $this->clearCache();

        return $result > 0;
    }

    /**
     * Paginate the models.
     *
     * Returns a paginated result set. This method does not use caching
     * as pagination results are typically dynamic.
     *
     * @param  int  $perPage  Number of items per page
     * @param  array<string>  $columns  The columns to retrieve
     * @return LengthAwarePaginator Paginated result set
     *
     * @example
     * $users = $repository->paginate(10);
     * $users = $repository->paginate(25, ['id', 'name']);
     */
    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator
    {
        return $this->query()->paginate($perPage, $columns);
    }

    /**
     * Get the query builder instance.
     *
     * Returns a fresh query builder instance for the model. This allows
     * for custom query building while maintaining repository abstraction.
     *
     * @return Builder<T> Eloquent query builder instance
     *
     * @example
     * $activeUsers = $repository->query()
     *     ->where('active', true)
     *     ->orderBy('name')
     *     ->get();
     */
    public function query(): Builder
    {
        return $this->model::query();
    }

    /**
     * Clear all cache for this repository.
     *
     * Invalidates all cached data for this repository. This is automatically
     * called when models are created, updated, or deleted.
     *
     * @internal This method is protected and should not be called directly
     */
    protected function clearCache(): void
    {
        $pattern = $this->getCacheKeyPrefix().'*';
        Cache::forget($pattern);
    }

    /**
     * Generate a cache key for a method call.
     *
     * Creates a unique cache key based on the method name and parameters.
     *
     * @param  string  $method  The method name
     * @param  mixed  ...$params  The method parameters
     * @return string The generated cache key
     *
     * @internal This method is protected and should not be called directly
     */
    protected function getCacheKey(string $method, mixed ...$params): string
    {
        $prefix = $this->getCacheKeyPrefix();
        $paramsHash = md5(serialize($params));

        return "{$prefix}:{$method}:{$paramsHash}";
    }

    /**
     * Get the cache key prefix for this repository.
     *
     * Returns a prefix based on the model class name, ensuring cache keys
     * are unique per repository type.
     *
     * @return string The cache key prefix
     *
     * @internal This method is protected and should not be called directly
     */
    protected function getCacheKeyPrefix(): string
    {
        return 'repository:'.class_basename($this->model);
    }
}
