<?php

declare(strict_types=1);

namespace LaravelPlus\Ecommerce\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use LaravelPlus\Ecommerce\Enums\OrderStatus;

final class UpdateOrderStatusRequest extends FormRequest
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
        $statuses = implode(',', array_column(OrderStatus::cases(), 'value'));

        return [
            'status' => ['required', 'string', "in:{$statuses}"],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'status.required' => 'The order status is required.',
            'status.in' => 'The selected status is invalid.',
        ];
    }
}
