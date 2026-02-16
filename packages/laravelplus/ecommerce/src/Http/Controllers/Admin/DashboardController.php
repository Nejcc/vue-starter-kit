<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Http\Controllers\Admin;

use Inertia\Inertia;
use Inertia\Response;
use LaravelPlus\Ecommerce\Enums\OrderStatus;
use LaravelPlus\Ecommerce\Models\Category;
use LaravelPlus\Ecommerce\Models\Order;
use LaravelPlus\Ecommerce\Models\Product;
use LaravelPlus\Ecommerce\Models\ProductVariant;

final class DashboardController
{
    public function index(): Response
    {
        return Inertia::render('admin/ecommerce/Dashboard', [
            'stats' => $this->getStats(),
            'recentProducts' => $this->getRecentProducts(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function getStats(): array
    {
        $currency = config('ecommerce.currency.code', 'USD');

        return [
            'totalProducts' => Product::count(),
            'activeProducts' => Product::query()->active()->count(),
            'draftProducts' => Product::query()->where('status', 'draft')->count(),
            'featuredProducts' => Product::query()->featured()->count(),
            'totalCategories' => Category::count(),
            'activeCategories' => Category::query()->active()->count(),
            'totalVariants' => ProductVariant::count(),
            'lowStockProducts' => Product::query()
                ->where('stock_quantity', '>', 0)
                ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
                ->count(),
            'outOfStockProducts' => Product::query()
                ->where('stock_quantity', '<=', 0)
                ->count(),
            'currency' => $currency,
            'totalOrders' => Order::query()->count(),
            'pendingOrders' => Order::query()->withStatus(OrderStatus::Pending)->count(),
            'completedOrders' => Order::query()->withStatus(OrderStatus::Completed)->count(),
            'revenue' => (int) Order::query()->completed()->sum('total'),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getRecentProducts(): array
    {
        return Product::with('categories')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'price' => $product->price,
                'currency' => $product->currency,
                'status' => $product->status,
                'stock_quantity' => $product->stock_quantity,
                'is_featured' => $product->is_featured,
                'created_at' => $product->created_at->toISOString(),
                'categories' => $product->categories->map(fn ($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                ])->toArray(),
            ])
            ->toArray();
    }
}
