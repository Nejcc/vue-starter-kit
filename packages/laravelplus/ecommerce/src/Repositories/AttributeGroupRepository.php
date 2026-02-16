<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use LaravelPlus\Ecommerce\Contracts\AttributeGroupRepositoryInterface;
use LaravelPlus\Ecommerce\Models\AttributeGroup;

/**
 * Attribute group repository implementation.
 *
 * Provides data access methods for AttributeGroup models.
 */
final class AttributeGroupRepository implements AttributeGroupRepositoryInterface
{
    /**
     * @var class-string<AttributeGroup>
     */
    public private(set) string $modelClass = AttributeGroup::class;

    /**
     * @return Builder<AttributeGroup>
     */
    public function query(): Builder
    {
        return $this->modelClass::query();
    }

    public function find(int $id): ?AttributeGroup
    {
        return $this->query()->find($id);
    }

    public function findOrFail(int $id): AttributeGroup
    {
        return $this->query()->findOrFail($id);
    }

    public function findBySlug(string $slug): ?AttributeGroup
    {
        return $this->query()->where('slug', $slug)->first();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): AttributeGroup
    {
        return $this->modelClass::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(AttributeGroup $group, array $data): AttributeGroup
    {
        $group->update($data);

        return $group->refresh();
    }

    public function delete(AttributeGroup $group): bool
    {
        return $group->delete();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()->ordered()->paginate($perPage);
    }

    public function search(string $term, int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->where('name', 'like', "%{$term}%")
            ->ordered()
            ->paginate($perPage);
    }

    /**
     * @return Collection<int, AttributeGroup>
     */
    public function getActive(): Collection
    {
        return $this->query()->active()->ordered()->get();
    }
}
