<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SeoSharedPropsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        if (class_exists(\LaravelPlus\GlobalSettings\Models\Setting::class)) {
            \LaravelPlus\GlobalSettings\Models\Setting::flushCache();
        }
    }

    public function test_seo_props_are_shared_on_public_pages(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->has('seo')
            ->has('seo.gtmId')
            ->has('seo.defaultDescription')
            ->has('seo.siteName')
        );
    }

    public function test_seo_props_are_shared_on_auth_pages(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/settings/profile');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->has('seo')
            ->has('seo.gtmId')
            ->has('seo.defaultDescription')
            ->has('seo.siteName')
        );
    }

    public function test_gtm_id_defaults_to_empty_string(): void
    {
        config(['seo.gtm_id' => '']);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('seo.gtmId', '')
        );
    }

    public function test_gtm_id_from_config(): void
    {
        config(['seo.gtm_id' => 'GTM-TEST123']);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('seo.gtmId', 'GTM-TEST123')
        );
    }

    public function test_site_name_from_app_config(): void
    {
        config(['app.name' => 'Test App']);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('seo.siteName', 'Test App')
        );
    }

    public function test_default_description_from_config(): void
    {
        config(['seo.default_meta_description' => 'Test description']);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('seo.defaultDescription', 'Test description')
        );
    }
}
