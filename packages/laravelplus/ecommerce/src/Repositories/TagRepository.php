<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use LaravelPlus\Ecommerce\Contracts\TagRepositoryInterface;
use LaravelPlus\Ecommerce\Models\Tag;

/**
 * Tag repository implementation.
 *
 * Provides data access methods for Tag models.
 */
final class TagRepository implements TagRepositoryInterface
{
    /**
     * @var class-string<Tag>
     */
    public private(set) string $modelClass = Tag::class;

    /**
     * @return Builder<Tag>
     */
    public function query(): Builder
    {
        return $this->modelClass::query();
    }

    public function find(int $id): ?Tag
    {
        return $this->query()->find($id);
    }

    public function findOrFail(int $id): Tag
    {
        return $this->query()->findOrFail($id);
    }

    public function findBySlug(string $slug): ?Tag
    {
        return $this->query()->where('slug', $slug)->first();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Tag
    {
        return $this->modelClass::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Tag $tag, array $data): Tag
    {
        $tag->update($data);

        return $tag->refresh();
    }

    public function delete(Tag $tag): bool
    {
        return $tag->delete();
    }

    /**
     * @return Collection<int, Tag>
     */
    public function all(): Collection
    {
        return $this->query()->ordered()->get();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()->ordered()->paginate($perPage);
    }

    public function search(string $term, int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->where('name', 'like', "%{$term}%")
            ->ordered()
            ->paginate($perPage);
    }

    /**
     * @return Collection<int, Tag>
     */
    public function getByType(string $type): Collection
    {
        return $this->query()->ofType($type)->ordered()->get();
    }
}
