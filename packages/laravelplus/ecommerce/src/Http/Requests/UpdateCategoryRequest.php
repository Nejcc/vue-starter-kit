<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $categoryId = $this->route('category')?->id ?? $this->route('category');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', "unique:ecommerce_categories,slug,{$categoryId}"],
            'description' => ['nullable', 'string', 'max:1000'],
            'parent_id' => [
                'nullable',
                'integer',
                'exists:ecommerce_categories,id',
                Rule::notIn([$categoryId]),
            ],
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
            'parent_id.not_in' => 'A category cannot be its own parent.',
        ];
    }
}
