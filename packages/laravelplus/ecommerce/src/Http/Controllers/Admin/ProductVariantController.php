<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use LaravelPlus\Ecommerce\Http\Requests\StoreProductVariantRequest;
use LaravelPlus\Ecommerce\Http\Requests\UpdateProductVariantRequest;
use LaravelPlus\Ecommerce\Models\Product;
use LaravelPlus\Ecommerce\Models\ProductVariant;
use LaravelPlus\Ecommerce\Services\ProductVariantService;

/**
 * Admin product variant controller.
 *
 * Handles CRUD operations for product variants in the admin panel.
 * Middleware is applied via route definitions in routes/admin.php.
 */
final class ProductVariantController
{
    public function __construct(
        private(set) ProductVariantService $variantService,
    ) {}

    /**
     * Store a new variant for a product.
     */
    public function store(StoreProductVariantRequest $request, Product $product): RedirectResponse
    {
        $this->variantService->create($product, $request->validated());

        return redirect()->route('admin.ecommerce.products.edit', $product)
            ->with('status', 'Variant created successfully.');
    }

    /**
     * Update the specified variant.
     */
    public function update(UpdateProductVariantRequest $request, Product $product, ProductVariant $variant): RedirectResponse
    {
        $this->variantService->update($variant, $request->validated());

        return redirect()->route('admin.ecommerce.products.edit', $product)
            ->with('status', 'Variant updated successfully.');
    }

    /**
     * Remove the specified variant.
     */
    public function destroy(Product $product, ProductVariant $variant): RedirectResponse
    {
        $this->variantService->delete($variant);

        return redirect()->route('admin.ecommerce.products.edit', $product)
            ->with('status', 'Variant deleted successfully.');
    }

    /**
     * Reorder variants.
     */
    public function reorder(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['integer', 'min:0'],
        ]);

        $this->variantService->reorder($validated['order']);

        return redirect()->route('admin.ecommerce.products.edit', $product)
            ->with('status', 'Variants reordered successfully.');
    }
}
