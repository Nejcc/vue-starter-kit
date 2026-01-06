<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Setting;
use App\SettingRole;
use Illuminate\Database\Seeder;

final class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Setting::updateOrCreate(
            ['key' => 'registration_enabled'],
            [
                'value' => '0',
                'field_type' => 'checkbox',
                'label' => 'Allow New User Registration',
                'description' => 'Enable or disable public user registration. When disabled, only administrators can create new user accounts.',
                'role' => SettingRole::System->value,
            ]
        );
    }
}
