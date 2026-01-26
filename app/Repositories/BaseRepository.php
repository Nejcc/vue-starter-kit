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
     * @param  mixed  $id  The primary key value
     * @param  array<string>  $columns  The columns to retrieve
     * @return TModel|null The model instance or null if not found
     *
     * @example
     * $user = $repository->find(1);
     * $user = $repository->find(1, ['id', 'name']);
     */
    public function find(mixed $id, array $columns = ['*']): ?Model
    {
        return $this->query()->find($id, $columns);
    }

    /**
     * Create a new model instance.
     *
     * @param  array<string, mixed>  $attributes  The model attributes
     * @return TModel The newly created model instance
     *
     * @example
     * $user = $repository->create([
     *     'name' => 'John Doe',
     *     'email' => 'john@example.com',
     * ]);
     */
    public function create(array $attributes): Model
    {
        return $this->modelClass::create($attributes);
    }

    /**
     * Update a model by its primary key.
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
        $model = $this->find($id);

        if (!$model) {
            return false;
        }

        return $model->update($attributes);
    }

    /**
     * Delete a model by its primary key.
     *
     * @param  mixed  $id  The primary key value
     * @return bool True if the deletion was successful, false otherwise
     *
     * @example
     * $success = $repository->delete(1);
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
     * @param  array<string>  $columns  The columns to retrieve
     * @return Collection<int, TModel> Collection of all models
     *
     * @example
     * $allUsers = $repository->all();
     * $allUsers = $repository->all(['id', 'name']);
     */
    public function all(array $columns = ['*']): Collection
    {
        return $this->query()->get($columns);
    }

    /**
     * Paginate the query results.
     *
     * @param  int  $perPage  Number of items per page
     * @param  array<string>  $columns  The columns to retrieve
     * @return \Illuminate\Pagination\LengthAwarePaginator Paginated result set
     *
     * @example
     * $users = $repository->paginate(10);
     * $users = $repository->paginate(25, ['id', 'name']);
     */
    public function paginate(int $perPage = 15, array $columns = ['*']): \Illuminate\Pagination\LengthAwarePaginator
    {
        return $this->query()->paginate($perPage, $columns);
    }

    /**
     * Find a model by its primary key or throw an exception.
     *
     * Unlike find(), this method throws a ModelNotFoundException if the model
     * is not found.
     *
     * @param  mixed  $id  The primary key value
     * @param  array<string>  $columns  The columns to retrieve
     * @return TModel The model instance
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
     * Find a model by given attributes.
     *
     * Searches for a model matching all provided attributes. Multiple attributes
     * are combined with AND logic.
     *
     * @param  array<string, mixed>  $attributes  Key-value pairs to search for
     * @param  array<string>  $columns  The columns to retrieve
     * @return TModel|null The first matching model or null if not found
     *
     * @example
     * $user = $repository->findBy(['email' => 'user@example.com']);
     * $user = $repository->findBy(['name' => 'John', 'active' => true]);
     */
    public function findBy(array $attributes, array $columns = ['*']): ?Model
    {
        return $this->query()->where($attributes)->first($columns);
    }

    /**
     * Find all models by given attributes.
     *
     * Returns a collection of all models matching the provided attributes.
     * Multiple attributes are combined with AND logic.
     *
     * @param  array<string, mixed>  $attributes  Key-value pairs to search for
     * @param  array<string>  $columns  The columns to retrieve
     * @return Collection<int, TModel> Collection of matching models
     *
     * @example
     * $users = $repository->findAllBy(['active' => true]);
     */
    public function findAllBy(array $attributes, array $columns = ['*']): Collection
    {
        return $this->query()->where($attributes)->get($columns);
    }
}
