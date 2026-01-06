<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

final class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            ['name' => 'view users', 'group_name' => 'users'],
            ['name' => 'create users', 'group_name' => 'users'],
            ['name' => 'edit users', 'group_name' => 'users'],
            ['name' => 'delete users', 'group_name' => 'users'],
            ['name' => 'impersonate', 'group_name' => 'users'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                ['group_name' => $permission['group_name']]
            );
        }
    }
}
