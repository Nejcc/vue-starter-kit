<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Http\Requests\AbstractFormRequest;
use App\Models\Permission;
use Illuminate\Validation\Rule;

final class UpdatePermissionRequest extends AbstractFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $permissionId = $this->route('permission');

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique(Permission::class)->ignore($permissionId)],
            'group_name' => ['nullable', 'string', 'max:255'],
        ];
    }
}
