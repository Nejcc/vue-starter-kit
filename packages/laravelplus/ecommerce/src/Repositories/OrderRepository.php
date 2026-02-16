<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use LaravelPlus\Ecommerce\Contracts\OrderRepositoryInterface;
use LaravelPlus\Ecommerce\Enums\OrderStatus;
use LaravelPlus\Ecommerce\Models\Order;

/**
 * Order repository implementation.
 *
 * Provides data access methods for Order models.
 */
final class OrderRepository implements OrderRepositoryInterface
{
    /**
     * @var class-string<Order>
     */
    public private(set) string $modelClass = Order::class;

    /**
     * @return Builder<Order>
     */
    public function query(): Builder
    {
        return $this->modelClass::query();
    }

    public function find(int $id): ?Order
    {
        return $this->query()->find($id);
    }

    public function findOrFail(int $id): Order
    {
        return $this->query()->findOrFail($id);
    }

    public function findByUuid(string $uuid): ?Order
    {
        return $this->query()->where('uuid', $uuid)->first();
    }

    public function findByOrderNumber(string $orderNumber): ?Order
    {
        return $this->query()->where('order_number', $orderNumber)->first();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Order
    {
        return $this->modelClass::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Order $order, array $data): Order
    {
        $order->update($data);

        return $order->refresh();
    }

    public function delete(Order $order): bool
    {
        return $order->delete();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()->latest()->paginate($perPage);
    }

    public function search(string $term, int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->where(function (Builder $q) use ($term): void {
                $q->where('order_number', 'like', "%{$term}%")
                    ->orWhereHas('user', function (Builder $uq) use ($term): void {
                        $uq->where('name', 'like', "%{$term}%")
                            ->orWhere('email', 'like', "%{$term}%");
                    });
            })
            ->latest()
            ->paginate($perPage);
    }

    public function filterByStatus(OrderStatus $status, int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->withStatus($status)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * @return Collection<int, Order>
     */
    public function getForUser(int $userId): Collection
    {
        return $this->query()->forUser($userId)->latest()->get();
    }
}
