<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'email' => 'admin@laravelplus.com',
                'name' => 'Super Admin',
                'password' => 'password',
                'role' => 'super-admin',
            ],
            [
                'email' => 'manager@laravelplus.com',
                'name' => 'Admin User',
                'password' => 'password',
                'role' => 'admin',
            ],
            [
                'email' => 'user@laravelplus.com',
                'name' => 'Regular User',
                'password' => 'password',
                'role' => 'user',
            ],
        ];

        foreach ($users as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make($userData['password']),
                    'email_verified_at' => now(),
                ]
            );

            $user->assignRole($userData['role']);
        }
    }
}
