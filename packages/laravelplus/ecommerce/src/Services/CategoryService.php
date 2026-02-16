<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use LaravelPlus\Ecommerce\Contracts\CategoryRepositoryInterface;
use LaravelPlus\Ecommerce\Models\Category;

/**
 * Category service implementation.
 *
 * Provides business logic for category management.
 */
final class CategoryService
{
    public function __construct(
        private(set) CategoryRepositoryInterface $repository,
    ) {}

    /**
     * List categories with optional search.
     */
    public function list(int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        if ($search) {
            return $this->repository->query()
                ->where(function ($q) use ($search): void {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                })
                ->ordered()
                ->paginate($perPage);
        }

        return $this->repository->paginate($perPage);
    }

    /**
     * Create a new category.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Category
    {
        return DB::transaction(fn () => $this->repository->create($data));
    }

    /**
     * Update a category.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Category $category, array $data): Category
    {
        return DB::transaction(fn () => $this->repository->update($category, $data));
    }

    /**
     * Delete a category.
     */
    public function delete(Category $category): bool
    {
        return DB::transaction(fn () => $this->repository->delete($category));
    }

    /**
     * Get root categories.
     *
     * @return Collection<int, Category>
     */
    public function getRootCategories(): Collection
    {
        return $this->repository->getRootCategories();
    }

    /**
     * Get the category tree.
     *
     * @return Collection<int, Category>
     */
    public function getTree(): Collection
    {
        return $this->repository->getTree();
    }

    /**
     * Reorder categories.
     *
     * @param  array<int, int>  $order
     */
    public function reorder(array $order): void
    {
        DB::transaction(fn () => $this->repository->reorder($order));
    }

    /**
     * Move a category to a new parent.
     */
    public function move(Category $category, ?int $parentId): Category
    {
        return DB::transaction(fn () => $this->repository->update($category, ['parent_id' => $parentId]));
    }

    /**
     * Find a category by slug.
     */
    public function findBySlug(string $slug): ?Category
    {
        return $this->repository->findBySlug($slug);
    }
}
