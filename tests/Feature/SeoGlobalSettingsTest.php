<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SeoGlobalSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (!class_exists(\LaravelPlus\GlobalSettings\Models\Setting::class)) {
            $this->markTestSkipped('GlobalSettings package is not installed.');
        }
    }

    public function test_gtm_id_from_global_settings_overrides_config(): void
    {
        config(['seo.gtm_id' => 'GTM-CONFIG']);

        \LaravelPlus\GlobalSettings\Models\Setting::updateOrCreate(
            ['key' => 'google_tag_manager_id'],
            ['value' => 'GTM-SETTINGS']
        );

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('seo.gtmId', 'GTM-SETTINGS')
        );
    }

    public function test_empty_global_setting_falls_back_to_config(): void
    {
        config(['seo.gtm_id' => 'GTM-FALLBACK']);

        \LaravelPlus\GlobalSettings\Models\Setting::updateOrCreate(
            ['key' => 'google_tag_manager_id'],
            ['value' => '']
        );

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('seo.gtmId', 'GTM-FALLBACK')
        );
    }

    public function test_description_from_site_description_setting(): void
    {
        config(['seo.default_meta_description' => 'Config description']);

        \LaravelPlus\GlobalSettings\Models\Setting::updateOrCreate(
            ['key' => 'site_description'],
            ['value' => 'Settings description']
        );

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('seo.defaultDescription', 'Settings description')
        );
    }
}
