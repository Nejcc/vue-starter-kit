<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use LaravelPlus\Ecommerce\Contracts\OrderRepositoryInterface;
use LaravelPlus\Ecommerce\Enums\OrderStatus;
use LaravelPlus\Ecommerce\Models\Order;

/**
 * Order service implementation.
 *
 * Provides business logic for order management.
 */
final class OrderService
{
    public function __construct(
        private(set) OrderRepositoryInterface $repository,
    ) {}

    /**
     * List orders with optional search and status filter.
     */
    public function list(int $perPage = 15, ?string $search = null, ?OrderStatus $status = null): LengthAwarePaginator
    {
        if ($search) {
            return $this->repository->search($search, $perPage);
        }

        if ($status) {
            return $this->repository->filterByStatus($status, $perPage);
        }

        return $this->repository->paginate($perPage);
    }

    /**
     * Create a new order with items.
     *
     * @param  array<string, mixed>  $data
     * @param  array<int, array<string, mixed>>  $items
     */
    public function create(array $data, array $items = []): Order
    {
        return DB::transaction(function () use ($data, $items): Order {
            $order = $this->repository->create($data);

            foreach ($items as $item) {
                $order->items()->create($item);
            }

            if (! empty($items)) {
                $this->recalculateTotals($order);
            }

            return $order;
        });
    }

    /**
     * Update order status with transition validation.
     */
    public function updateStatus(Order $order, OrderStatus $newStatus): Order
    {
        if (! $order->canTransitionTo($newStatus)) {
            throw new \InvalidArgumentException(
                "Cannot transition order from {$order->status->label()} to {$newStatus->label()}."
            );
        }

        $data = ['status' => $newStatus];

        if ($newStatus === OrderStatus::Completed) {
            $data['completed_at'] = now();
        }

        if ($newStatus === OrderStatus::Cancelled) {
            $data['cancelled_at'] = now();
        }

        return DB::transaction(fn () => $this->repository->update($order, $data));
    }

    /**
     * Cancel an order.
     */
    public function cancel(Order $order): Order
    {
        return $this->updateStatus($order, OrderStatus::Cancelled);
    }

    /**
     * Complete an order.
     */
    public function complete(Order $order): Order
    {
        return $this->updateStatus($order, OrderStatus::Completed);
    }

    /**
     * Recalculate order totals from items.
     */
    public function recalculateTotals(Order $order): Order
    {
        $subtotal = $order->items()->sum('total');
        $total = $subtotal + $order->tax - $order->discount + $order->shipping_cost;

        return $this->repository->update($order, [
            'subtotal' => (int) $subtotal,
            'total' => (int) max(0, $total),
        ]);
    }

    /**
     * Delete an order (soft delete).
     */
    public function delete(Order $order): bool
    {
        return DB::transaction(fn () => $this->repository->delete($order));
    }

    /**
     * Find by UUID.
     */
    public function findByUuid(string $uuid): ?Order
    {
        return $this->repository->findByUuid($uuid);
    }

    /**
     * Find by order number.
     */
    public function findByOrderNumber(string $orderNumber): ?Order
    {
        return $this->repository->findByOrderNumber($orderNumber);
    }

    /**
     * Get orders for a user.
     *
     * @return Collection<int, Order>
     */
    public function getForUser(int $userId): Collection
    {
        return $this->repository->getForUser($userId);
    }

    /**
     * Get order statistics.
     *
     * @return array{totalOrders: int, pendingOrders: int, completedOrders: int, revenue: int}
     */
    public function getOrderStats(): array
    {
        return [
            'totalOrders' => Order::query()->count(),
            'pendingOrders' => Order::query()->pending()->count(),
            'completedOrders' => Order::query()->completed()->count(),
            'revenue' => (int) Order::query()->completed()->sum('total'),
        ];
    }
}
