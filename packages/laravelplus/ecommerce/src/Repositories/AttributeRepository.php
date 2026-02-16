<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use LaravelPlus\Ecommerce\Contracts\AttributeRepositoryInterface;
use LaravelPlus\Ecommerce\Models\Attribute;

/**
 * Attribute repository implementation.
 *
 * Provides data access methods for Attribute models.
 */
final class AttributeRepository implements AttributeRepositoryInterface
{
    /**
     * @var class-string<Attribute>
     */
    public private(set) string $modelClass = Attribute::class;

    /**
     * @return Builder<Attribute>
     */
    public function query(): Builder
    {
        return $this->modelClass::query();
    }

    public function find(int $id): ?Attribute
    {
        return $this->query()->find($id);
    }

    public function findOrFail(int $id): Attribute
    {
        return $this->query()->findOrFail($id);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Attribute
    {
        return $this->modelClass::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Attribute $attribute, array $data): Attribute
    {
        $attribute->update($data);

        return $attribute->refresh();
    }

    public function delete(Attribute $attribute): bool
    {
        return $attribute->delete();
    }

    /**
     * @return Collection<int, Attribute>
     */
    public function getByGroup(int $groupId): Collection
    {
        return $this->query()
            ->where('attribute_group_id', $groupId)
            ->ordered()
            ->get();
    }

    /**
     * @return Collection<int, Attribute>
     */
    public function getFilterable(): Collection
    {
        return $this->query()->active()->filterable()->ordered()->get();
    }
}
