<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use LaravelPlus\Ecommerce\Enums\OrderStatus;
use LaravelPlus\Ecommerce\Http\Requests\UpdateOrderStatusRequest;
use LaravelPlus\Ecommerce\Models\Order;
use LaravelPlus\Ecommerce\Services\OrderService;

/**
 * Admin order controller.
 *
 * Handles viewing and managing orders in the admin panel.
 */
final class OrderController
{
    public function __construct(
        private(set) OrderService $orderService,
    ) {}

    /**
     * Display a listing of orders.
     */
    public function index(Request $request): Response
    {
        $perPage = (int) config('ecommerce.per_page', 15);
        $search = $request->get('search');
        $statusFilter = $request->filled('status') ? OrderStatus::tryFrom($request->get('status')) : null;

        $orders = $this->orderService->list($perPage, $search, $statusFilter);

        return Inertia::render('admin/ecommerce/Orders', [
            'orders' => $orders,
            'statuses' => collect(OrderStatus::cases())->map(fn (OrderStatus $status) => [
                'value' => $status->value,
                'label' => $status->label(),
                'color' => $status->color(),
            ])->all(),
            'filters' => [
                'search' => $search ?? '',
                'status' => $statusFilter?->value,
            ],
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order): Response
    {
        $order->load(['items.product', 'items.productVariant', 'user']);

        $availableTransitions = collect(OrderStatus::cases())
            ->filter(fn (OrderStatus $status) => $order->canTransitionTo($status))
            ->map(fn (OrderStatus $status) => [
                'value' => $status->value,
                'label' => $status->label(),
                'color' => $status->color(),
            ])
            ->values()
            ->all();

        return Inertia::render('admin/ecommerce/Orders/Show', [
            'order' => $order,
            'availableTransitions' => $availableTransitions,
        ]);
    }

    /**
     * Update the order status.
     */
    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): RedirectResponse
    {
        $newStatus = OrderStatus::from($request->validated('status'));

        try {
            $this->orderService->updateStatus($order, $newStatus);

            return redirect()->route('admin.ecommerce.orders.show', $order)
                ->with('status', "Order status updated to {$newStatus->label()}.");
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->withErrors(['status' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified order.
     */
    public function destroy(Order $order): RedirectResponse
    {
        $this->orderService->delete($order);

        return redirect()->route('admin.ecommerce.orders.index')
            ->with('status', 'Order deleted successfully.');
    }
}
