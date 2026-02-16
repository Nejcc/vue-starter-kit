<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use LaravelPlus\Ecommerce\Models\Tag;

interface TagRepositoryInterface
{
    public function query(): Builder;

    public function find(int $id): ?Tag;

    public function findOrFail(int $id): Tag;

    public function findBySlug(string $slug): ?Tag;

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Tag;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Tag $tag, array $data): Tag;

    public function delete(Tag $tag): bool;

    /**
     * @return Collection<int, Tag>
     */
    public function all(): Collection;

    public function paginate(int $perPage = 15): LengthAwarePaginator;

    /**
     * Search tags by name.
     */
    public function search(string $term, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get tags by type.
     *
     * @return Collection<int, Tag>
     */
    public function getByType(string $type): Collection;
}
