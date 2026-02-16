<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreCategoryRequest extends FormRequest
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
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:ecommerce_categories,slug'],
            'description' => ['nullable', 'string', 'max:1000'],
            'parent_id' => ['nullable', 'integer', 'exists:ecommerce_categories,id'],
            'image' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The category name is required.',
            'slug.unique' => 'This slug is already taken.',
            'parent_id.exists' => 'The selected parent category does not exist.',
        ];
    }
}
