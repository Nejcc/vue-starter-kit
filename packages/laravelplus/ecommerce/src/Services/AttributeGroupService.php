<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use LaravelPlus\Ecommerce\Contracts\AttributeGroupRepositoryInterface;
use LaravelPlus\Ecommerce\Models\AttributeGroup;

/**
 * Attribute group service implementation.
 *
 * Provides business logic for attribute group management.
 */
final class AttributeGroupService
{
    public function __construct(
        private(set) AttributeGroupRepositoryInterface $repository,
    ) {}

    /**
     * List attribute groups with optional search.
     */
    public function list(int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        if ($search) {
            return $this->repository->search($search, $perPage);
        }

        return $this->repository->paginate($perPage);
    }

    /**
     * Create a new attribute group.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): AttributeGroup
    {
        return DB::transaction(fn () => $this->repository->create($data));
    }

    /**
     * Update an attribute group.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(AttributeGroup $group, array $data): AttributeGroup
    {
        return DB::transaction(fn () => $this->repository->update($group, $data));
    }

    /**
     * Delete an attribute group.
     */
    public function delete(AttributeGroup $group): bool
    {
        return DB::transaction(fn () => $this->repository->delete($group));
    }

    /**
     * Get active attribute groups.
     *
     * @return Collection<int, AttributeGroup>
     */
    public function getActive(): Collection
    {
        return $this->repository->getActive();
    }
}
