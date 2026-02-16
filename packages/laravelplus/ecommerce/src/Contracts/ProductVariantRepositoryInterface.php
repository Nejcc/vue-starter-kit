<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use LaravelPlus\Ecommerce\Models\ProductVariant;

interface ProductVariantRepositoryInterface
{
    public function query(): Builder;

    public function find(int $id): ?ProductVariant;

    public function findOrFail(int $id): ProductVariant;

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): ProductVariant;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(ProductVariant $variant, array $data): ProductVariant;

    public function delete(ProductVariant $variant): bool;

    /**
     * Get all variants for a product.
     *
     * @return Collection<int, ProductVariant>
     */
    public function getForProduct(int $productId): Collection;

    /**
     * Reorder variants by updating their sort_order.
     *
     * @param  array<int, int>  $order  Map of variant_id => sort_order
     */
    public function reorder(array $order): void;
}
