<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use LaravelPlus\Ecommerce\Enums\AttributeType;
use LaravelPlus\Ecommerce\Http\Requests\StoreAttributeGroupRequest;
use LaravelPlus\Ecommerce\Http\Requests\UpdateAttributeGroupRequest;
use LaravelPlus\Ecommerce\Models\AttributeGroup;
use LaravelPlus\Ecommerce\Services\AttributeGroupService;

/**
 * Admin attribute group controller.
 *
 * Handles CRUD operations for attribute groups in the admin panel.
 */
final class AttributeGroupController
{
    public function __construct(
        private(set) AttributeGroupService $attributeGroupService,
    ) {}

    /**
     * Display a listing of attribute groups.
     */
    public function index(Request $request): Response
    {
        $perPage = (int) config('ecommerce.per_page', 15);
        $search = $request->get('search');

        $attributeGroups = $this->attributeGroupService->list($perPage, $search);

        return Inertia::render('admin/ecommerce/Attributes', [
            'attributeGroups' => $attributeGroups,
            'filters' => [
                'search' => $search ?? '',
            ],
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Show the form for creating a new attribute group.
     */
    public function create(): Response
    {
        return Inertia::render('admin/ecommerce/Attributes/Create', [
            'attributeTypes' => collect(AttributeType::cases())->map(fn (AttributeType $type) => [
                'value' => $type->value,
                'label' => $type->label(),
            ])->all(),
        ]);
    }

    /**
     * Store a newly created attribute group.
     */
    public function store(StoreAttributeGroupRequest $request): RedirectResponse
    {
        $this->attributeGroupService->create($request->validated());

        return redirect()->route('admin.ecommerce.attributes.index')
            ->with('status', 'Attribute group created successfully.');
    }

    /**
     * Show the form for editing an attribute group.
     */
    public function edit(AttributeGroup $attributeGroup): Response
    {
        $attributeGroup->load(['attributes' => fn ($q) => $q->ordered()]);

        return Inertia::render('admin/ecommerce/Attributes/Edit', [
            'attributeGroup' => $attributeGroup,
            'attributeTypes' => collect(AttributeType::cases())->map(fn (AttributeType $type) => [
                'value' => $type->value,
                'label' => $type->label(),
            ])->all(),
        ]);
    }

    /**
     * Update the specified attribute group.
     */
    public function update(UpdateAttributeGroupRequest $request, AttributeGroup $attributeGroup): RedirectResponse
    {
        $this->attributeGroupService->update($attributeGroup, $request->validated());

        return redirect()->route('admin.ecommerce.attributes.index')
            ->with('status', 'Attribute group updated successfully.');
    }

    /**
     * Remove the specified attribute group.
     */
    public function destroy(AttributeGroup $attributeGroup): RedirectResponse
    {
        $this->attributeGroupService->delete($attributeGroup);

        return redirect()->route('admin.ecommerce.attributes.index')
            ->with('status', 'Attribute group deleted successfully.');
    }
}
