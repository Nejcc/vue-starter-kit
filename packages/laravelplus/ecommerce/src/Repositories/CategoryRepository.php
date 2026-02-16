<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use LaravelPlus\Ecommerce\Contracts\CategoryRepositoryInterface;
use LaravelPlus\Ecommerce\Models\Category;

/**
 * Category repository implementation.
 *
 * Provides data access methods for Category models.
 */
final class CategoryRepository implements CategoryRepositoryInterface
{
    /**
     * @var class-string<Category>
     */
    public private(set) string $modelClass = Category::class;

    /**
     * @return Builder<Category>
     */
    public function query(): Builder
    {
        return $this->modelClass::query();
    }

    public function find(int $id): ?Category
    {
        return $this->query()->find($id);
    }

    public function findOrFail(int $id): Category
    {
        return $this->query()->findOrFail($id);
    }

    public function findBySlug(string $slug): ?Category
    {
        return $this->query()->where('slug', $slug)->first();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Category
    {
        return $this->modelClass::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Category $category, array $data): Category
    {
        $category->update($data);

        return $category->refresh();
    }

    public function delete(Category $category): bool
    {
        return $category->delete();
    }

    /**
     * @return Collection<int, Category>
     */
    public function all(): Collection
    {
        return $this->query()->ordered()->get();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()->ordered()->paginate($perPage);
    }

    /**
     * @return Collection<int, Category>
     */
    public function getRootCategories(): Collection
    {
        return $this->query()->root()->ordered()->get();
    }

    /**
     * @return Collection<int, Category>
     */
    public function getTree(): Collection
    {
        return $this->query()
            ->root()
            ->ordered()
            ->with('children')
            ->get();
    }

    /**
     * @param  array<int, int>  $order
     */
    public function reorder(array $order): void
    {
        foreach ($order as $categoryId => $sortOrder) {
            $this->query()
                ->where('id', $categoryId)
                ->update(['sort_order' => $sortOrder]);
        }
    }
}
