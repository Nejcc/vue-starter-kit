<?php

declare(strict_types=1);

namespace App\Http\Requests\Settings;

use App\Http\Requests\AbstractFormRequest;
use Illuminate\Validation\Rules\Password;

final class PasswordUpdateRequest extends AbstractFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'current_password.required' => 'The current password is required.',
            'current_password.current_password' => 'The current password is incorrect.',
            'password.required' => 'The new password is required.',
            'password.confirmed' => 'The password confirmation does not match.',
        ];
    }
}
