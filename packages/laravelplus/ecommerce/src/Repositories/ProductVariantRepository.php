<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use LaravelPlus\Ecommerce\Contracts\ProductVariantRepositoryInterface;
use LaravelPlus\Ecommerce\Models\ProductVariant;

/**
 * Product variant repository implementation.
 *
 * Provides data access methods for ProductVariant models.
 */
final class ProductVariantRepository implements ProductVariantRepositoryInterface
{
    /**
     * @var class-string<ProductVariant>
     */
    public private(set) string $modelClass = ProductVariant::class;

    /**
     * @return Builder<ProductVariant>
     */
    public function query(): Builder
    {
        return $this->modelClass::query();
    }

    public function find(int $id): ?ProductVariant
    {
        return $this->query()->find($id);
    }

    public function findOrFail(int $id): ProductVariant
    {
        return $this->query()->findOrFail($id);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): ProductVariant
    {
        return $this->modelClass::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(ProductVariant $variant, array $data): ProductVariant
    {
        $variant->update($data);

        return $variant->refresh();
    }

    public function delete(ProductVariant $variant): bool
    {
        return $variant->delete();
    }

    /**
     * @return Collection<int, ProductVariant>
     */
    public function getForProduct(int $productId): Collection
    {
        return $this->query()
            ->where('product_id', $productId)
            ->ordered()
            ->get();
    }

    /**
     * @param  array<int, int>  $order
     */
    public function reorder(array $order): void
    {
        foreach ($order as $variantId => $sortOrder) {
            $this->query()
                ->where('id', $variantId)
                ->update(['sort_order' => $sortOrder]);
        }
    }
}
