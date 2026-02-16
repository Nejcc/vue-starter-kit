<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use LaravelPlus\Ecommerce\Enums\OrderStatus;
use LaravelPlus\Ecommerce\Models\Order;

interface OrderRepositoryInterface
{
    public function query(): Builder;

    public function find(int $id): ?Order;

    public function findOrFail(int $id): Order;

    public function findByUuid(string $uuid): ?Order;

    public function findByOrderNumber(string $orderNumber): ?Order;

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Order;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Order $order, array $data): Order;

    public function delete(Order $order): bool;

    public function paginate(int $perPage = 15): LengthAwarePaginator;

    /**
     * Search orders by order number, user name, or email.
     */
    public function search(string $term, int $perPage = 15): LengthAwarePaginator;

    public function filterByStatus(OrderStatus $status, int $perPage = 15): LengthAwarePaginator;

    /**
     * @return Collection<int, Order>
     */
    public function getForUser(int $userId): Collection;
}
