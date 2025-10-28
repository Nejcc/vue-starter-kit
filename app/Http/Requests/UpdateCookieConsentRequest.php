<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCookieConsentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $categories = array_keys(config('cookie.categories', []));

        $rules = [];

        foreach ($categories as $category) {
            $rules[$category] = 'boolean';
        }

        return $rules;
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'essential.boolean' => 'Essential cookies must be enabled.',
            'analytics.boolean' => 'Analytics cookies must be a boolean value.',
            'marketing.boolean' => 'Marketing cookies must be a boolean value.',
            'preferences.boolean' => 'Preference cookies must be a boolean value.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Ensure essential cookies are always enabled
        $this->merge([
            'essential' => true,
        ]);
    }

    /**
     * Determine if the request expects a JSON response.
     */
    public function expectsJson(): bool
    {
        return true;
    }
}
