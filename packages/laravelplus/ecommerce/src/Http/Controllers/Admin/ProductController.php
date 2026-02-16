<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use LaravelPlus\Ecommerce\Http\Requests\StoreProductRequest;
use LaravelPlus\Ecommerce\Http\Requests\UpdateProductRequest;
use LaravelPlus\Ecommerce\Models\AttributeGroup;
use LaravelPlus\Ecommerce\Models\Category;
use LaravelPlus\Ecommerce\Models\Product;
use LaravelPlus\Ecommerce\Models\Tag;
use LaravelPlus\Ecommerce\Services\ProductService;

/**
 * Admin product controller.
 *
 * Handles CRUD operations for products in the admin panel.
 * Middleware is applied via route definitions in routes/admin.php.
 */
final class ProductController
{
    public function __construct(
        private(set) ProductService $productService,
    ) {}

    /**
     * Display a listing of products.
     */
    public function index(Request $request): Response
    {
        $perPage = (int) config('ecommerce.per_page', 15);
        $search = $request->get('search');
        $categoryId = $request->filled('category') ? (int) $request->get('category') : null;

        $products = $this->productService->list($perPage, $search, $categoryId);

        return Inertia::render('admin/ecommerce/Products', [
            'products' => $products,
            'categories' => Category::query()->active()->ordered()->get(['id', 'name']),
            'filters' => [
                'search' => $search ?? '',
                'category' => $categoryId,
            ],
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Show the form for creating a new product.
     */
    public function create(): Response
    {
        return Inertia::render('admin/ecommerce/Products/Create', [
            'categories' => Category::query()->active()->ordered()->get(['id', 'name']),
            'tags' => Tag::query()->ordered()->get(['id', 'name', 'slug']),
            'attributeGroups' => AttributeGroup::query()->active()->ordered()
                ->with(['attributes' => fn ($q) => $q->active()->ordered()])
                ->get(),
        ]);
    }

    /**
     * Store a newly created product.
     */
    public function store(StoreProductRequest $request): RedirectResponse
    {
        $this->productService->create($request->validated());

        return redirect()->route('admin.ecommerce.products.index')
            ->with('status', 'Product created successfully.');
    }

    /**
     * Show the form for editing a product.
     */
    public function edit(Product $product): Response
    {
        $product->load([
            'categories',
            'tags',
            'attributes',
            'variants' => fn ($q) => $q->ordered(),
        ]);

        return Inertia::render('admin/ecommerce/Products/Edit', [
            'product' => $product,
            'categories' => Category::query()->active()->ordered()->get(['id', 'name']),
            'tags' => Tag::query()->ordered()->get(['id', 'name', 'slug']),
            'attributeGroups' => AttributeGroup::query()->active()->ordered()
                ->with(['attributes' => fn ($q) => $q->active()->ordered()])
                ->get(),
        ]);
    }

    /**
     * Update the specified product.
     */
    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $this->productService->update($product, $request->validated());

        return redirect()->route('admin.ecommerce.products.index')
            ->with('status', 'Product updated successfully.');
    }

    /**
     * Remove the specified product.
     */
    public function destroy(Product $product): RedirectResponse
    {
        $this->productService->delete($product);

        return redirect()->route('admin.ecommerce.products.index')
            ->with('status', 'Product deleted successfully.');
    }
}
