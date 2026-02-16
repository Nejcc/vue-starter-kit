<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use LaravelPlus\Ecommerce\Http\Requests\StoreTagRequest;
use LaravelPlus\Ecommerce\Http\Requests\UpdateTagRequest;
use LaravelPlus\Ecommerce\Models\Tag;
use LaravelPlus\Ecommerce\Services\TagService;

/**
 * Admin tag controller.
 *
 * Handles CRUD operations for tags in the admin panel.
 */
final class TagController
{
    public function __construct(
        private(set) TagService $tagService,
    ) {}

    /**
     * Display a listing of tags.
     */
    public function index(Request $request): Response
    {
        $perPage = (int) config('ecommerce.per_page', 15);
        $search = $request->get('search');

        $tags = $this->tagService->list($perPage, $search);

        return Inertia::render('admin/ecommerce/Tags', [
            'tags' => $tags,
            'filters' => [
                'search' => $search ?? '',
            ],
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Show the form for creating a new tag.
     */
    public function create(): Response
    {
        return Inertia::render('admin/ecommerce/Tags/Create');
    }

    /**
     * Store a newly created tag.
     */
    public function store(StoreTagRequest $request): RedirectResponse
    {
        $this->tagService->create($request->validated());

        return redirect()->route('admin.ecommerce.tags.index')
            ->with('status', 'Tag created successfully.');
    }

    /**
     * Show the form for editing a tag.
     */
    public function edit(Tag $tag): Response
    {
        return Inertia::render('admin/ecommerce/Tags/Edit', [
            'tag' => $tag,
        ]);
    }

    /**
     * Update the specified tag.
     */
    public function update(UpdateTagRequest $request, Tag $tag): RedirectResponse
    {
        $this->tagService->update($tag, $request->validated());

        return redirect()->route('admin.ecommerce.tags.index')
            ->with('status', 'Tag updated successfully.');
    }

    /**
     * Remove the specified tag.
     */
    public function destroy(Tag $tag): RedirectResponse
    {
        $this->tagService->delete($tag);

        return redirect()->route('admin.ecommerce.tags.index')
            ->with('status', 'Tag deleted successfully.');
    }
}
