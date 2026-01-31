<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Constants\RoleNames;
use App\Http\Requests\AbstractFormRequest;
use App\Models\Role;
use Illuminate\Validation\Rule;

final class StoreRoleRequest extends AbstractFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique(Role::class),
                Rule::notIn([RoleNames::SUPER_ADMIN]), // Prevent creating super-admin role
            ],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.not_in' => 'The super-admin role cannot be created. It is a system role.',
        ];
    }
}
