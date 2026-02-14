<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Constants\RoleNames;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

final class CacheControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $superAdminRole = Role::create(['name' => RoleNames::SUPER_ADMIN]);
        $this->admin = User::factory()->create();
        $this->admin->assignRole($superAdminRole);
    }

    public function test_guests_cannot_access_cache_page(): void
    {
        $this->get(route('admin.cache.index'))
            ->assertRedirect(route('login'));
    }

    public function test_regular_users_cannot_access_cache_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.cache.index'))
            ->assertForbidden();
    }

    public function test_admin_can_view_cache_page(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.cache.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('admin/Cache/Index')
                ->has('driver')
                ->has('stats')
                ->has('items')
                ->has('maintenance')
            );
    }

    public function test_admin_role_can_access_cache_page(): void
    {
        $adminRole = Role::create(['name' => RoleNames::ADMIN]);
        $admin = User::factory()->create();
        $admin->assignRole($adminRole);

        $this->actingAs($admin)
            ->get(route('admin.cache.index'))
            ->assertOk();
    }

    public function test_cache_page_returns_driver_info(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.cache.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('driver.default')
                ->has('driver.stores')
                ->has('driver.prefix')
            );
    }

    public function test_cache_page_returns_stats(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.cache.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('stats.items')
                ->has('stats.expired')
                ->has('stats.active')
            );
    }

    public function test_cache_page_returns_maintenance_status(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.cache.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('maintenance.is_down', false)
            );
    }

    public function test_admin_can_clear_application_cache(): void
    {
        Cache::put('test-key', 'test-value', 60);

        $this->actingAs($this->admin)
            ->post(route('admin.cache.clear'))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertNull(Cache::get('test-key'));
    }

    public function test_admin_can_clear_views(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.cache.clear-views'))
            ->assertRedirect()
            ->assertSessionHas('success');
    }

    public function test_admin_can_clear_routes(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.cache.clear-routes'))
            ->assertRedirect()
            ->assertSessionHas('success');
    }

    public function test_admin_can_clear_config(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.cache.clear-config'))
            ->assertRedirect()
            ->assertSessionHas('success');
    }

    public function test_admin_can_clear_all_caches(): void
    {
        Cache::put('test-key', 'test-value', 60);

        $this->actingAs($this->admin)
            ->post(route('admin.cache.clear-all'))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertNull(Cache::get('test-key'));
    }

    public function test_guests_cannot_clear_cache(): void
    {
        $this->post(route('admin.cache.clear'))
            ->assertRedirect(route('login'));
    }

    public function test_regular_users_cannot_clear_cache(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('admin.cache.clear'))
            ->assertForbidden();
    }

    public function test_admin_can_toggle_maintenance_mode_on(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.cache.maintenance'))
            ->assertRedirect()
            ->assertSessionHas('success');

        // Clean up - bring app back up
        $this->app->instance('app.isDownForMaintenance', false);
        if (file_exists(storage_path('framework/down'))) {
            unlink(storage_path('framework/down'));
        }
    }

    public function test_admin_can_toggle_maintenance_mode_with_secret(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.cache.maintenance'), ['secret' => 'my-secret'])
            ->assertRedirect()
            ->assertSessionHas('success');

        // Clean up
        if (file_exists(storage_path('framework/down'))) {
            unlink(storage_path('framework/down'));
        }
    }

    public function test_cache_items_array_is_returned(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.cache.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('items')
            );
    }

    public function test_cache_stats_default_to_zero_for_non_database_driver(): void
    {
        // Test environment uses array driver, so stats should be 0
        $this->actingAs($this->admin)
            ->get(route('admin.cache.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('stats.items', 0)
                ->where('stats.expired', 0)
                ->where('stats.active', 0)
            );
    }
}
