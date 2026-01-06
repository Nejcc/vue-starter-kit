<?php

declare(strict_types=1);

namespace App\Http\Requests\Settings;

use App\Http\Requests\AbstractFormRequest;

final class RegistrationSettingRequest extends AbstractFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'registration_enabled' => ['required', 'boolean'],
        ];
    }
}
