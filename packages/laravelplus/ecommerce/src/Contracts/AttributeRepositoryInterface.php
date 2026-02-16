<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use LaravelPlus\Ecommerce\Models\Attribute;

interface AttributeRepositoryInterface
{
    public function query(): Builder;

    public function find(int $id): ?Attribute;

    public function findOrFail(int $id): Attribute;

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Attribute;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Attribute $attribute, array $data): Attribute;

    public function delete(Attribute $attribute): bool;

    /**
     * @return Collection<int, Attribute>
     */
    public function getByGroup(int $groupId): Collection;

    /**
     * @return Collection<int, Attribute>
     */
    public function getFilterable(): Collection;
}
