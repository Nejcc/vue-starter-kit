<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use LaravelPlus\Ecommerce\Contracts\ProductRepositoryInterface;
use LaravelPlus\Ecommerce\Models\Product;

/**
 * Product repository implementation.
 *
 * Provides data access methods for Product models.
 */
final class ProductRepository implements ProductRepositoryInterface
{
    /**
     * @var class-string<Product>
     */
    public private(set) string $modelClass = Product::class;

    /**
     * @return Builder<Product>
     */
    public function query(): Builder
    {
        return $this->modelClass::query();
    }

    public function find(int $id): ?Product
    {
        return $this->query()->find($id);
    }

    public function findOrFail(int $id): Product
    {
        return $this->query()->findOrFail($id);
    }

    public function findBySlug(string $slug): ?Product
    {
        return $this->query()->where('slug', $slug)->first();
    }

    public function findBySku(string $sku): ?Product
    {
        return $this->query()->where('sku', $sku)->first();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Product
    {
        return $this->modelClass::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Product $product, array $data): Product
    {
        $product->update($data);

        return $product->refresh();
    }

    public function delete(Product $product): bool
    {
        return $product->delete();
    }

    /**
     * @return Collection<int, Product>
     */
    public function all(): Collection
    {
        return $this->query()->get();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()->latest()->paginate($perPage);
    }

    public function search(string $term, int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->where(function (Builder $q) use ($term): void {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('sku', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%");
            })
            ->latest()
            ->paginate($perPage);
    }

    public function filterByCategory(int $categoryId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->inCategory($categoryId)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * @return Collection<int, Product>
     */
    public function getActive(): Collection
    {
        return $this->query()->published()->get();
    }

    /**
     * @return Collection<int, Product>
     */
    public function getFeatured(): Collection
    {
        return $this->query()->published()->featured()->get();
    }
}
