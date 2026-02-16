<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use LaravelPlus\Ecommerce\Enums\ProductStatus;

final class StoreProductRequest extends FormRequest
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
        $statuses = implode(',', array_column(ProductStatus::cases(), 'value'));

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:ecommerce_products,slug'],
            'sku' => ['nullable', 'string', 'max:100', 'unique:ecommerce_products,sku'],
            'description' => ['nullable', 'string', 'max:65535'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'price' => ['required', 'integer', 'min:0'],
            'compare_at_price' => ['nullable', 'integer', 'min:0'],
            'cost_price' => ['nullable', 'integer', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'status' => ['nullable', 'string', "in:{$statuses}"],
            'stock_quantity' => ['nullable', 'integer', 'min:0'],
            'low_stock_threshold' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'is_digital' => ['nullable', 'boolean'],
            'has_variants' => ['nullable', 'boolean'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'dimensions' => ['nullable', 'array'],
            'images' => ['nullable', 'array'],
            'metadata' => ['nullable', 'array'],
            'published_at' => ['nullable', 'date'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:ecommerce_categories,id'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['integer', 'exists:ecommerce_tags,id'],
            'attributes' => ['nullable', 'array'],
            'attributes.*' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The product name is required.',
            'price.required' => 'The product price is required.',
            'sku.unique' => 'This SKU is already in use.',
            'slug.unique' => 'This slug is already taken.',
        ];
    }
}
