<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use LaravelPlus\Ecommerce\Http\Requests\StoreAttributeRequest;
use LaravelPlus\Ecommerce\Http\Requests\UpdateAttributeRequest;
use LaravelPlus\Ecommerce\Models\Attribute;
use LaravelPlus\Ecommerce\Models\AttributeGroup;
use LaravelPlus\Ecommerce\Services\AttributeService;

/**
 * Admin attribute controller.
 *
 * Handles CRUD operations for attributes nested under attribute groups.
 */
final class AttributeController
{
    public function __construct(
        private(set) AttributeService $attributeService,
    ) {}

    /**
     * Store a newly created attribute in the group.
     */
    public function store(StoreAttributeRequest $request, AttributeGroup $attributeGroup): RedirectResponse
    {
        $data = $request->validated();
        $data['attribute_group_id'] = $attributeGroup->id;

        $this->attributeService->create($data);

        return redirect()->route('admin.ecommerce.attributes.edit', $attributeGroup)
            ->with('status', 'Attribute created successfully.');
    }

    /**
     * Update the specified attribute.
     */
    public function update(UpdateAttributeRequest $request, AttributeGroup $attributeGroup, Attribute $attribute): RedirectResponse
    {
        $this->attributeService->update($attribute, $request->validated());

        return redirect()->route('admin.ecommerce.attributes.edit', $attributeGroup)
            ->with('status', 'Attribute updated successfully.');
    }

    /**
     * Remove the specified attribute.
     */
    public function destroy(AttributeGroup $attributeGroup, Attribute $attribute): RedirectResponse
    {
        $this->attributeService->delete($attribute);

        return redirect()->route('admin.ecommerce.attributes.edit', $attributeGroup)
            ->with('status', 'Attribute deleted successfully.');
    }
}
