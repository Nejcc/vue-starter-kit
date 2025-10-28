<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @template T of Model
 */
interface RepositoryInterface
{
    /**
     * Find a model by its primary key.
     *
     * @param  array<string>  $columns
     * @return T|null
     */
    public function find(mixed $id, array $columns = ['*']): ?Model;

    /**
     * Find a model by its primary key or throw an exception.
     *
     * @param  array<string>  $columns
     * @return T
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail(mixed $id, array $columns = ['*']): Model;

    /**
     * Find a model by the given attributes.
     *
     * @param  array<string, mixed>  $attributes
     * @param  array<string>  $columns
     * @return T|null
     */
    public function findBy(array $attributes, array $columns = ['*']): ?Model;

    /**
     * Find all models matching the given attributes.
     *
     * @param  array<string, mixed>  $attributes
     * @param  array<string>  $columns
     * @return Collection<int, T>
     */
    public function findAllBy(array $attributes, array $columns = ['*']): Collection;

    /**
     * Get all models.
     *
     * @param  array<string>  $columns
     * @return Collection<int, T>
     */
    public function all(array $columns = ['*']): Collection;

    /**
     * Create a new model instance.
     *
     * @param  array<string, mixed>  $attributes
     * @return T
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
     */
    public function delete(mixed $id): bool;

    /**
     * Paginate the models.
     *
     * @param  array<string>  $columns
     */
    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator;

    /**
     * Get the query builder instance.
     *
     * @return \Illuminate\Database\Eloquent\Builder<T>
     */
    public function query(): \Illuminate\Database\Eloquent\Builder;
}
