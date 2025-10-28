<?php

namespace App\Repositories;

use App\Contracts\RepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

/**
 * @template T of Model
 */
abstract class AbstractRepository implements RepositoryInterface
{
    /**
     * The model instance.
     *
     * @var class-string<T>
     */
    protected string $model;

    /**
     * Cache TTL in seconds.
     */
    protected int $cacheTtl = 3600;

    /**
     * Create a new repository instance.
     *
     * @param  class-string<T>  $model
     */
    public function __construct(string $model)
    {
        $this->model = $model;
    }

    /**
     * Find a model by its primary key.
     *
     * @param  array<string>  $columns
     * @return T|null
     */
    public function find(mixed $id, array $columns = ['*']): ?Model
    {
        $cacheKey = $this->getCacheKey('find', $id, $columns);

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($id, $columns) {
            return $this->query()->find($id, $columns);
        });
    }

    /**
     * Find a model by its primary key or throw an exception.
     *
     * @param  array<string>  $columns
     * @return T
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail(mixed $id, array $columns = ['*']): Model
    {
        return $this->query()->findOrFail($id, $columns);
    }

    /**
     * Find a model by the given attributes.
     *
     * @param  array<string, mixed>  $attributes
     * @param  array<string>  $columns
     * @return T|null
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
     * @param  array<string, mixed>  $attributes
     * @param  array<string>  $columns
     * @return Collection<int, T>
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
     * @param  array<string>  $columns
     * @return Collection<int, T>
     */
    public function all(array $columns = ['*']): Collection
    {
        $cacheKey = $this->getCacheKey('all', $columns);

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($columns) {
            return $this->query()->get($columns);
        });
    }

    /**
     * Create a new model instance.
     *
     * @param  array<string, mixed>  $attributes
     * @return T
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
     * @param  array<string, mixed>  $attributes
     */
    public function update(mixed $id, array $attributes): bool
    {
        $result = $this->query()->where('id', $id)->update($attributes);
        $this->clearCache();

        return $result > 0;
    }

    /**
     * Delete a model by its primary key.
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
     * @param  array<string>  $columns
     */
    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator
    {
        return $this->query()->paginate($perPage, $columns);
    }

    /**
     * Get the query builder instance.
     *
     * @return Builder<T>
     */
    public function query(): Builder
    {
        return $this->model::query();
    }

    /**
     * Clear all cache for this repository.
     */
    protected function clearCache(): void
    {
        $pattern = $this->getCacheKeyPrefix().'*';
        Cache::forget($pattern);
    }

    /**
     * Generate a cache key.
     */
    protected function getCacheKey(string $method, mixed ...$params): string
    {
        $prefix = $this->getCacheKeyPrefix();
        $paramsHash = md5(serialize($params));

        return "{$prefix}:{$method}:{$paramsHash}";
    }

    /**
     * Get the cache key prefix for this repository.
     */
    protected function getCacheKeyPrefix(): string
    {
        return 'repository:'.class_basename($this->model);
    }
}
