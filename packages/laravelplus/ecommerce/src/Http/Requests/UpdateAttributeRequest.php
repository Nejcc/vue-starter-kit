<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use LaravelPlus\Ecommerce\Enums\AttributeType;

final class UpdateAttributeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        $types = implode(',', array_column(AttributeType::cases(), 'value'));
        $attributeId = $this->route('attribute')?->id ?? $this->route('attribute');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', "unique:ecommerce_attributes,slug,{$attributeId}"],
            'type' => ['required', 'string', "in:{$types}"],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_filterable' => ['nullable', 'boolean'],
            'is_required' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'values' => ['nullable', 'array', 'required_if:type,select'],
            'values.*' => ['string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The attribute name is required.',
            'type.required' => 'The attribute type is required.',
            'slug.unique' => 'This slug is already taken.',
            'values.required_if' => 'Select type attributes must have values.',
        ];
    }
}
