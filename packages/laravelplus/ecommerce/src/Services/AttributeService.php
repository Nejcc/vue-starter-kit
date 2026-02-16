<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use LaravelPlus\Ecommerce\Contracts\AttributeRepositoryInterface;
use LaravelPlus\Ecommerce\Models\Attribute;
use LaravelPlus\Ecommerce\Models\Product;

/**
 * Attribute service implementation.
 *
 * Provides business logic for attribute management.
 */
final class AttributeService
{
    public function __construct(
        private(set) AttributeRepositoryInterface $repository,
    ) {}

    /**
     * Create a new attribute.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Attribute
    {
        return DB::transaction(fn () => $this->repository->create($data));
    }

    /**
     * Update an attribute.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Attribute $attribute, array $data): Attribute
    {
        return DB::transaction(fn () => $this->repository->update($attribute, $data));
    }

    /**
     * Delete an attribute.
     */
    public function delete(Attribute $attribute): bool
    {
        return DB::transaction(fn () => $this->repository->delete($attribute));
    }

    /**
     * Get attributes by group.
     *
     * @return Collection<int, Attribute>
     */
    public function getByGroup(int $groupId): Collection
    {
        return $this->repository->getByGroup($groupId);
    }

    /**
     * Get filterable attributes.
     *
     * @return Collection<int, Attribute>
     */
    public function getFilterable(): Collection
    {
        return $this->repository->getFilterable();
    }

    /**
     * Sync attributes for a product.
     *
     * @param  array<int, string>  $attributeValues  [attribute_id => value]
     */
    public function syncProductAttributes(Product $product, array $attributeValues): void
    {
        $syncData = [];
        foreach ($attributeValues as $attributeId => $value) {
            $syncData[$attributeId] = ['value' => $value];
        }

        $product->attributes()->sync($syncData);
    }
}
