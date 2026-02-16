<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreProductVariantRequest extends FormRequest
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
            'sku' => ['nullable', 'string', 'max:100', 'unique:ecommerce_product_variants,sku'],
            'price' => ['nullable', 'integer', 'min:0'],
            'compare_at_price' => ['nullable', 'integer', 'min:0'],
            'stock_quantity' => ['nullable', 'integer', 'min:0'],
            'options' => ['required', 'array'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'images' => ['nullable', 'array'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The variant name is required.',
            'options.required' => 'The variant options are required.',
            'sku.unique' => 'This SKU is already in use.',
        ];
    }
}
