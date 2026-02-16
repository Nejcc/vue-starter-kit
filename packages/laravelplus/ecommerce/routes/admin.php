<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use LaravelPlus\Ecommerce\Http\Controllers\Admin\CategoryController;
use LaravelPlus\Ecommerce\Http\Controllers\Admin\DashboardController;
use LaravelPlus\Ecommerce\Http\Controllers\Admin\ProductController;
use LaravelPlus\Ecommerce\Http\Controllers\Admin\ProductVariantController;
use LaravelPlus\Ecommerce\Http\Controllers\Admin\AttributeController;
use LaravelPlus\Ecommerce\Http\Controllers\Admin\AttributeGroupController;
use LaravelPlus\Ecommerce\Http\Controllers\Admin\OrderController;
use LaravelPlus\Ecommerce\Http\Controllers\Admin\TagController;

$config = config('ecommerce.admin', []);
$prefix = $config['prefix'] ?? 'admin/ecommerce';
$middleware = $config['middleware'] ?? ['web', 'auth'];

Route::middleware($middleware)
    ->prefix($prefix)
    ->name('admin.ecommerce.')
    ->group(function (): void {
        // Dashboard
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Products
        Route::get('products', [ProductController::class, 'index'])->name('products.index');
        Route::get('products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('products', [ProductController::class, 'store'])->name('products.store');
        Route::get('products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

        // Product Variants
        Route::post('products/{product}/variants', [ProductVariantController::class, 'store'])->name('products.variants.store');
        Route::put('products/{product}/variants/{variant}', [ProductVariantController::class, 'update'])->name('products.variants.update');
        Route::delete('products/{product}/variants/{variant}', [ProductVariantController::class, 'destroy'])->name('products.variants.destroy');
        Route::post('products/{product}/variants/reorder', [ProductVariantController::class, 'reorder'])->name('products.variants.reorder');

        // Categories
        Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('categories/tree', [CategoryController::class, 'tree'])->name('categories.tree');
        Route::get('categories/create', [CategoryController::class, 'create'])->name('categories.create');
        Route::post('categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::get('categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
        Route::put('categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
        Route::post('categories/reorder', [CategoryController::class, 'reorder'])->name('categories.reorder');

        // Tags
        Route::get('tags', [TagController::class, 'index'])->name('tags.index');
        Route::get('tags/create', [TagController::class, 'create'])->name('tags.create');
        Route::post('tags', [TagController::class, 'store'])->name('tags.store');
        Route::get('tags/{tag}/edit', [TagController::class, 'edit'])->name('tags.edit');
        Route::put('tags/{tag}', [TagController::class, 'update'])->name('tags.update');
        Route::delete('tags/{tag}', [TagController::class, 'destroy'])->name('tags.destroy');

        // Attribute Groups
        Route::get('attributes', [AttributeGroupController::class, 'index'])->name('attributes.index');
        Route::get('attributes/create', [AttributeGroupController::class, 'create'])->name('attributes.create');
        Route::post('attributes', [AttributeGroupController::class, 'store'])->name('attributes.store');
        Route::get('attributes/{attributeGroup}/edit', [AttributeGroupController::class, 'edit'])->name('attributes.edit');
        Route::put('attributes/{attributeGroup}', [AttributeGroupController::class, 'update'])->name('attributes.update');
        Route::delete('attributes/{attributeGroup}', [AttributeGroupController::class, 'destroy'])->name('attributes.destroy');

        // Attributes (nested under groups)
        Route::post('attributes/{attributeGroup}/items', [AttributeController::class, 'store'])->name('attributes.items.store');
        Route::put('attributes/{attributeGroup}/items/{attribute}', [AttributeController::class, 'update'])->name('attributes.items.update');
        Route::delete('attributes/{attributeGroup}/items/{attribute}', [AttributeController::class, 'destroy'])->name('attributes.items.destroy');

        // Orders
        Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
        Route::delete('orders/{order}', [OrderController::class, 'destroy'])->name('orders.destroy');
    });
