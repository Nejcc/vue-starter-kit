<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use LaravelPlus\Ecommerce\Contracts\ProductRepositoryInterface;
use LaravelPlus\Ecommerce\Enums\ProductStatus;
use LaravelPlus\Ecommerce\Models\Product;

/**
 * Product service implementation.
 *
 * Provides business logic for product management.
 */
final class ProductService
{
    public function __construct(
        private(set) ProductRepositoryInterface $repository,
    ) {}

    /**
     * List products with optional search and category filter.
     */
    public function list(int $perPage = 15, ?string $search = null, ?int $categoryId = null): LengthAwarePaginator
    {
        if ($search) {
            return $this->repository->search($search, $perPage);
        }

        if ($categoryId) {
            return $this->repository->filterByCategory($categoryId, $perPage);
        }

        return $this->repository->paginate($perPage);
    }

    /**
     * Create a new product.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Product
    {
        return DB::transaction(function () use ($data): Product {
            $categoryIds = $data['category_ids'] ?? [];
            $tagIds = $data['tag_ids'] ?? [];
            $attributes = $data['attributes'] ?? [];
            unset($data['category_ids'], $data['tag_ids'], $data['attributes']);

            $product = $this->repository->create($data);

            if (! empty($categoryIds)) {
                $product->categories()->sync($categoryIds);
            }

            if (! empty($tagIds)) {
                $product->tags()->sync($tagIds);
            }

            if (! empty($attributes)) {
                $syncData = [];
                foreach ($attributes as $attributeId => $value) {
                    if ($value !== null && $value !== '') {
                        $syncData[$attributeId] = ['value' => $value];
                    }
                }
                $product->attributes()->sync($syncData);
            }

            return $product;
        });
    }

    /**
     * Update a product.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Product $product, array $data): Product
    {
        return DB::transaction(function () use ($product, $data): Product {
            $categoryIds = $data['category_ids'] ?? null;
            $tagIds = $data['tag_ids'] ?? null;
            $attributes = $data['attributes'] ?? null;
            unset($data['category_ids'], $data['tag_ids'], $data['attributes']);

            $product = $this->repository->update($product, $data);

            if ($categoryIds !== null) {
                $product->categories()->sync($categoryIds);
            }

            if ($tagIds !== null) {
                $product->tags()->sync($tagIds);
            }

            if ($attributes !== null) {
                $syncData = [];
                foreach ($attributes as $attributeId => $value) {
                    if ($value !== null && $value !== '') {
                        $syncData[$attributeId] = ['value' => $value];
                    }
                }
                $product->attributes()->sync($syncData);
            }

            return $product;
        });
    }

    /**
     * Delete a product.
     */
    public function delete(Product $product): bool
    {
        return DB::transaction(fn () => $this->repository->delete($product));
    }

    /**
     * Update stock quantity.
     */
    public function updateStock(Product $product, int $quantity): Product
    {
        return $this->repository->update($product, ['stock_quantity' => $quantity]);
    }

    /**
     * Increment stock quantity.
     */
    public function incrementStock(Product $product, int $amount = 1): Product
    {
        $product->increment('stock_quantity', $amount);

        return $product->refresh();
    }

    /**
     * Decrement stock quantity.
     */
    public function decrementStock(Product $product, int $amount = 1): Product
    {
        $newQuantity = max(0, $product->stock_quantity - $amount);

        return $this->repository->update($product, ['stock_quantity' => $newQuantity]);
    }

    /**
     * Sync categories for a product.
     *
     * @param  array<int>  $categoryIds
     */
    public function syncCategories(Product $product, array $categoryIds): void
    {
        $product->categories()->sync($categoryIds);
    }

    /**
     * Publish a product.
     */
    public function publish(Product $product): Product
    {
        return $this->repository->update($product, [
            'status' => ProductStatus::Active,
            'is_active' => true,
            'published_at' => now(),
        ]);
    }

    /**
     * Unpublish a product (set to draft).
     */
    public function unpublish(Product $product): Product
    {
        return $this->repository->update($product, [
            'status' => ProductStatus::Draft,
        ]);
    }

    /**
     * Toggle featured status.
     */
    public function toggleFeatured(Product $product): Product
    {
        return $this->repository->update($product, [
            'is_featured' => ! $product->is_featured,
        ]);
    }

    /**
     * Get active/published products.
     *
     * @return Collection<int, Product>
     */
    public function getActive(): Collection
    {
        return $this->repository->getActive();
    }

    /**
     * Get featured products.
     *
     * @return Collection<int, Product>
     */
    public function getFeatured(): Collection
    {
        return $this->repository->getFeatured();
    }

    /**
     * Find a product by slug.
     */
    public function findBySlug(string $slug): ?Product
    {
        return $this->repository->findBySlug($slug);
    }

    /**
     * Find a product by SKU.
     */
    public function findBySku(string $sku): ?Product
    {
        return $this->repository->findBySku($sku);
    }
}
