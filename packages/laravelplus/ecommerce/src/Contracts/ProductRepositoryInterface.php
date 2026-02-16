<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use LaravelPlus\Ecommerce\Models\Product;

interface ProductRepositoryInterface
{
    public function query(): Builder;

    public function find(int $id): ?Product;

    public function findOrFail(int $id): Product;

    public function findBySlug(string $slug): ?Product;

    public function findBySku(string $sku): ?Product;

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Product;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Product $product, array $data): Product;

    public function delete(Product $product): bool;

    /**
     * @return Collection<int, Product>
     */
    public function all(): Collection;

    public function paginate(int $perPage = 15): LengthAwarePaginator;

    /**
     * Search products by name, SKU, or description.
     */
    public function search(string $term, int $perPage = 15): LengthAwarePaginator;

    /**
     * Filter products by category.
     */
    public function filterByCategory(int $categoryId, int $perPage = 15): LengthAwarePaginator;

    /**
     * @return Collection<int, Product>
     */
    public function getActive(): Collection;

    /**
     * @return Collection<int, Product>
     */
    public function getFeatured(): Collection;
}
