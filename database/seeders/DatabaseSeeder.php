<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $seeders = [
            PermissionSeeder::class,
            RoleSeeder::class,
        ];

        if (class_exists(\LaravelPlus\GlobalSettings\Database\Seeders\SettingsSeeder::class)) {
            $seeders[] = \LaravelPlus\GlobalSettings\Database\Seeders\SettingsSeeder::class;
        }

        $seeders[] = UserSeeder::class;

        $this->call($seeders);
    }
}
