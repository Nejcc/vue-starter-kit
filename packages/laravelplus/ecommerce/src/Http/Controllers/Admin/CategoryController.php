<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use LaravelPlus\Ecommerce\Http\Requests\StoreCategoryRequest;
use LaravelPlus\Ecommerce\Http\Requests\UpdateCategoryRequest;
use LaravelPlus\Ecommerce\Models\Category;
use LaravelPlus\Ecommerce\Services\CategoryService;

/**
 * Admin category controller.
 *
 * Handles CRUD operations for categories in the admin panel.
 * Middleware is applied via route definitions in routes/admin.php.
 */
final class CategoryController
{
    public function __construct(
        private(set) CategoryService $categoryService,
    ) {}

    /**
     * Display a listing of categories.
     */
    public function index(Request $request): Response
    {
        $perPage = (int) config('ecommerce.per_page', 15);
        $search = $request->get('search');

        $categories = $this->categoryService->list($perPage, $search);

        return Inertia::render('admin/ecommerce/Categories', [
            'categories' => $categories,
            'filters' => [
                'search' => $search ?? '',
            ],
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Display the category tree.
     */
    public function tree(): Response
    {
        return Inertia::render('admin/ecommerce/Categories/Tree', [
            'tree' => $this->categoryService->getTree(),
        ]);
    }

    /**
     * Show the form for creating a new category.
     */
    public function create(): Response
    {
        return Inertia::render('admin/ecommerce/Categories/Create', [
            'parentCategories' => Category::query()->active()->root()->ordered()->get(['id', 'name']),
        ]);
    }

    /**
     * Store a newly created category.
     */
    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $this->categoryService->create($request->validated());

        return redirect()->route('admin.ecommerce.categories.index')
            ->with('status', 'Category created successfully.');
    }

    /**
     * Show the form for editing a category.
     */
    public function edit(Category $category): Response
    {
        $category->load(['parent', 'children']);

        return Inertia::render('admin/ecommerce/Categories/Edit', [
            'category' => $category,
            'parentCategories' => Category::query()
                ->active()
                ->where('id', '!=', $category->id)
                ->ordered()
                ->get(['id', 'name']),
        ]);
    }

    /**
     * Update the specified category.
     */
    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $this->categoryService->update($category, $request->validated());

        return redirect()->route('admin.ecommerce.categories.index')
            ->with('status', 'Category updated successfully.');
    }

    /**
     * Remove the specified category.
     */
    public function destroy(Category $category): RedirectResponse
    {
        $this->categoryService->delete($category);

        return redirect()->route('admin.ecommerce.categories.index')
            ->with('status', 'Category deleted successfully.');
    }

    /**
     * Reorder categories.
     */
    public function reorder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['integer', 'min:0'],
        ]);

        $this->categoryService->reorder($validated['order']);

        return redirect()->route('admin.ecommerce.categories.index')
            ->with('status', 'Categories reordered successfully.');
    }
}
