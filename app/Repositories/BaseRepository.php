<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\RepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Base repository class.
 *
 * Provides common CRUD operations for all repositories.
 * Uses PHP 8.4+ features including constructor property promotion.
 *
 * @template TModel of Model
 */
abstract class BaseRepository implements RepositoryInterface
{
    /**
     * The model class name.
     *
     * @var class-string<TModel>
     */
    protected readonly string $modelClass;

    /**
     * Create a new repository instance.
     *
     * @param  class-string<TModel>  $modelClass
     */
    public function __construct(string $modelClass)
    {
        $this->modelClass = $modelClass;
    }

    /**
     * Get a new query builder instance.
     *
     * @return Builder<TModel>
     */
    public function query(): Builder
    {
        return $this->modelClass::query();
    }

    /**
     * Find a model by its primary key.
     *
     * @param  array<string>  $columns
     * @return TModel|null
     */
    public function find(mixed $id, array $columns = ['*']): ?Model
    {
        return $this->query()->find($id, $columns);
    }

    /**
     * Create a new model instance.
     *
     * @param  array<string, mixed>  $attributes
     * @return TModel
     */
    public function create(array $attributes): Model
    {
        return $this->modelClass::create($attributes);
    }

    /**
     * Update a model by its primary key.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function update(mixed $id, array $attributes): bool
    {
        $model = $this->find($id);

        if (!$model) {
            return false;
        }

        return $model->update($attributes);
    }

    /**
     * Delete a model by its primary key.
     */
    public function delete(mixed $id): bool
    {
        $model = $this->find($id);

        if (!$model) {
            return false;
        }

        return $model->delete();
    }

    /**
     * Get all models.
     *
     * @param  array<string>  $columns
     * @return Collection<int, TModel>
     */
    public function all(array $columns = ['*']): Collection
    {
        return $this->query()->get($columns);
    }

    /**
     * Paginate the query results.
     *
     * @param  array<string>  $columns
     */
    public function paginate(int $perPage = 15, array $columns = ['*']): \Illuminate\Pagination\LengthAwarePaginator
    {
        return $this->query()->paginate($perPage, $columns);
    }

    /**
     * Find a model by its primary key or throw an exception.
     *
     * @param  array<string>  $columns
     * @return TModel
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail(mixed $id, array $columns = ['*']): Model
    {
        return $this->query()->findOrFail($id, $columns);
    }

    /**
     * Find a model by given attributes.
     *
     * @param  array<string, mixed>  $attributes
     * @param  array<string>  $columns
     * @return TModel|null
     */
    public function findBy(array $attributes, array $columns = ['*']): ?Model
    {
        return $this->query()->where($attributes)->first($columns);
    }

    /**
     * Find all models by given attributes.
     *
     * @param  array<string, mixed>  $attributes
     * @param  array<string>  $columns
     * @return Collection<int, TModel>
     */
    public function findAllBy(array $attributes, array $columns = ['*']): Collection
    {
        return $this->query()->where($attributes)->get($columns);
    }
}
