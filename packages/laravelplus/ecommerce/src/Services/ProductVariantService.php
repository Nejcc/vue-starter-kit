<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use LaravelPlus\Ecommerce\Contracts\ProductVariantRepositoryInterface;
use LaravelPlus\Ecommerce\Models\Product;
use LaravelPlus\Ecommerce\Models\ProductVariant;

/**
 * Product variant service implementation.
 *
 * Provides business logic for product variant management.
 */
final class ProductVariantService
{
    public function __construct(
        private(set) ProductVariantRepositoryInterface $repository,
    ) {}

    /**
     * Get all variants for a product.
     *
     * @return Collection<int, ProductVariant>
     */
    public function getForProduct(int $productId): Collection
    {
        return $this->repository->getForProduct($productId);
    }

    /**
     * Create a new variant.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(Product $product, array $data): ProductVariant
    {
        return DB::transaction(function () use ($product, $data): ProductVariant {
            $data['product_id'] = $product->id;
            $variant = $this->repository->create($data);

            if (! $product->has_variants) {
                $product->update(['has_variants' => true]);
            }

            return $variant;
        });
    }

    /**
     * Update a variant.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(ProductVariant $variant, array $data): ProductVariant
    {
        return DB::transaction(fn () => $this->repository->update($variant, $data));
    }

    /**
     * Delete a variant.
     */
    public function delete(ProductVariant $variant): bool
    {
        return DB::transaction(function () use ($variant): bool {
            $product = $variant->product;
            $result = $this->repository->delete($variant);

            // If no more active variants, unset has_variants
            if ($product->variants()->where('id', '!=', $variant->id)->count() === 0) {
                $product->update(['has_variants' => false]);
            }

            return $result;
        });
    }

    /**
     * Reorder variants.
     *
     * @param  array<int, int>  $order
     */
    public function reorder(array $order): void
    {
        DB::transaction(fn () => $this->repository->reorder($order));
    }

    /**
     * Update stock for a variant.
     */
    public function updateStock(ProductVariant $variant, int $quantity): ProductVariant
    {
        return $this->repository->update($variant, ['stock_quantity' => $quantity]);
    }

    /**
     * Increment stock for a variant.
     */
    public function incrementStock(ProductVariant $variant, int $amount = 1): ProductVariant
    {
        $variant->increment('stock_quantity', $amount);

        return $variant->refresh();
    }

    /**
     * Decrement stock for a variant.
     */
    public function decrementStock(ProductVariant $variant, int $amount = 1): ProductVariant
    {
        $newQuantity = max(0, $variant->stock_quantity - $amount);

        return $this->repository->update($variant, ['stock_quantity' => $newQuantity]);
    }
}
