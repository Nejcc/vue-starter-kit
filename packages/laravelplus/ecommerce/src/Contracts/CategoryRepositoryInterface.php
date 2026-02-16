<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use LaravelPlus\Ecommerce\Models\Category;

interface CategoryRepositoryInterface
{
    public function query(): Builder;

    public function find(int $id): ?Category;

    public function findOrFail(int $id): Category;

    public function findBySlug(string $slug): ?Category;

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Category;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Category $category, array $data): Category;

    public function delete(Category $category): bool;

    /**
     * @return Collection<int, Category>
     */
    public function all(): Collection;

    public function paginate(int $perPage = 15): LengthAwarePaginator;

    /**
     * @return Collection<int, Category>
     */
    public function getRootCategories(): Collection;

    /**
     * Get the full category tree (root categories with nested children).
     *
     * @return Collection<int, Category>
     */
    public function getTree(): Collection;

    /**
     * Reorder categories by updating their sort_order.
     *
     * @param  array<int, int>  $order  Map of category_id => sort_order
     */
    public function reorder(array $order): void;
}
