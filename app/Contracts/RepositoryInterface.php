<?php

declare(strict_types=1);

namespace App\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Base repository interface.
 *
 * @template TModel of Model
 */
interface RepositoryInterface
{
    /**
     * Get a new query builder instance.
     *
     * @return \Illuminate\Database\Eloquent\Builder<TModel>
     */
    public function query(): \Illuminate\Database\Eloquent\Builder;

    /**
     * Find a model by its primary key.
     *
     * @param  array<string>  $columns
     * @return TModel|null
     */
    public function find(mixed $id, array $columns = ['*']): ?Model;

    /**
     * Find a model by its primary key or throw an exception.
     *
     * @param  array<string>  $columns
     * @return TModel
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail(mixed $id, array $columns = ['*']): Model;

    /**
     * Find a model by given attributes.
     *
     * @param  array<string, mixed>  $attributes
     * @param  array<string>  $columns
     * @return TModel|null
     */
    public function findBy(array $attributes, array $columns = ['*']): ?Model;

    /**
     * Find all models by given attributes.
     *
     * @param  array<string, mixed>  $attributes
     * @param  array<string>  $columns
     * @return Collection<int, TModel>
     */
    public function findAllBy(array $attributes, array $columns = ['*']): Collection;

    /**
     * Create a new model instance.
     *
     * @param  array<string, mixed>  $attributes
     * @return TModel
     */
    public function create(array $attributes): Model;

    /**
     * Update a model by its primary key.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function update(mixed $id, array $attributes): bool;

    /**
     * Delete a model by its primary key.
     *
     */
    public function delete(mixed $id): bool;

    /**
     * Get all models.
     *
     * @param  array<string>  $columns
     * @return Collection<int, TModel>
     */
    public function all(array $columns = ['*']): Collection;

    /**
     * Paginate the query results.
     *
     * @param  array<string>  $columns
     */
    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator;
}
