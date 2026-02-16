<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use LaravelPlus\Ecommerce\Models\AttributeGroup;

interface AttributeGroupRepositoryInterface
{
    public function query(): Builder;

    public function find(int $id): ?AttributeGroup;

    public function findOrFail(int $id): AttributeGroup;

    public function findBySlug(string $slug): ?AttributeGroup;

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): AttributeGroup;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(AttributeGroup $group, array $data): AttributeGroup;

    public function delete(AttributeGroup $group): bool;

    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function search(string $term, int $perPage = 15): LengthAwarePaginator;

    /**
     * @return Collection<int, AttributeGroup>
     */
    public function getActive(): Collection;
}
