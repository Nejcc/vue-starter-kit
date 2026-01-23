<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

final class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'super-admin',
                'permissions' => ['view users', 'create users', 'edit users', 'delete users'],
            ],
            [
                'name' => 'admin',
                'permissions' => ['view users', 'create users', 'edit users', 'delete users'],
            ],
            [
                'name' => 'user',
                'permissions' => ['view users'],
            ],
        ];

        foreach ($roles as $roleData) {
            $role = Role::firstOrCreate(['name' => $roleData['name']]);
            $role->givePermissionTo($roleData['permissions']);
        }
    }
}
