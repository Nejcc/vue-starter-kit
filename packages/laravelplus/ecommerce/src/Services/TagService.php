<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use LaravelPlus\Ecommerce\Contracts\TagRepositoryInterface;
use LaravelPlus\Ecommerce\Models\Product;
use LaravelPlus\Ecommerce\Models\Tag;

/**
 * Tag service implementation.
 *
 * Provides business logic for tag management.
 */
final class TagService
{
    public function __construct(
        private(set) TagRepositoryInterface $repository,
    ) {}

    /**
     * List tags with optional search.
     */
    public function list(int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        if ($search) {
            return $this->repository->search($search, $perPage);
        }

        return $this->repository->paginate($perPage);
    }

    /**
     * Create a new tag.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Tag
    {
        return DB::transaction(fn () => $this->repository->create($data));
    }

    /**
     * Update a tag.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Tag $tag, array $data): Tag
    {
        return DB::transaction(fn () => $this->repository->update($tag, $data));
    }

    /**
     * Delete a tag.
     */
    public function delete(Tag $tag): bool
    {
        return DB::transaction(fn () => $this->repository->delete($tag));
    }

    /**
     * Find a tag by slug.
     */
    public function findBySlug(string $slug): ?Tag
    {
        return $this->repository->findBySlug($slug);
    }

    /**
     * Get all tags.
     *
     * @return Collection<int, Tag>
     */
    public function getAll(): Collection
    {
        return $this->repository->all();
    }

    /**
     * Get tags by type.
     *
     * @return Collection<int, Tag>
     */
    public function getByType(string $type): Collection
    {
        return $this->repository->getByType($type);
    }

    /**
     * Sync tags for a product.
     *
     * @param  array<int>  $tagIds
     */
    public function syncProductTags(Product $product, array $tagIds): void
    {
        $product->tags()->sync($tagIds);
    }
}
