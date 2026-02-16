<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Constants\RoleNames;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LaravelPlus\GlobalSettings\Models\Setting;
use Tests\TestCase;

final class PackagesControllerTest extends TestCase
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

    public function test_guests_cannot_access_packages_page(): void
    {
        $response = $this->get(route('admin.packages.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_regular_users_cannot_access_packages_page(): void
    {
        $user = User::factory()->create();
        $userRole = Role::create(['name' => RoleNames::USER]);
        $user->assignRole($userRole);

        $response = $this->actingAs($user)->get(route('admin.packages.index'));

        $response->assertStatus(403);
    }

    public function test_admin_can_view_packages_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.packages.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('admin/Packages/Index')
            ->has('packages')
            ->has('packages.0.key')
            ->has('packages.0.name')
            ->has('packages.0.enabled')
            ->has('packages.0.required')
        );
    }

    public function test_packages_list_contains_all_managed_packages(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.packages.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->has('packages', 6)
        );
    }

    public function test_admin_can_disable_a_package(): void
    {
        $response = $this->actingAs($this->admin)->patch(
            route('admin.packages.update', 'payments'),
            ['enabled' => false],
        );

        $response->assertRedirect(route('admin.packages.index'));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('settings', [
            'key' => 'package.payments.enabled',
            'value' => '0',
        ]);
    }

    public function test_admin_can_enable_a_package(): void
    {
        Setting::set('package.payments.enabled', '0');

        $response = $this->actingAs($this->admin)->patch(
            route('admin.packages.update', 'payments'),
            ['enabled' => true],
        );

        $response->assertRedirect(route('admin.packages.index'));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('settings', [
            'key' => 'package.payments.enabled',
            'value' => '1',
        ]);
    }

    public function test_cannot_disable_required_package(): void
    {
        $response = $this->actingAs($this->admin)->patch(
            route('admin.packages.update', 'globalSettings'),
            ['enabled' => false],
        );

        $response->assertRedirect(route('admin.packages.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseMissing('settings', [
            'key' => 'package.globalSettings.enabled',
        ]);
    }

    public function test_toggle_creates_audit_log(): void
    {
        $this->actingAs($this->admin)->patch(
            route('admin.packages.update', 'payments'),
            ['enabled' => false],
        );

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'package.toggled',
            'user_id' => $this->admin->id,
        ]);
    }

    public function test_invalid_package_key_returns_404(): void
    {
        $response = $this->actingAs($this->admin)->patch(
            route('admin.packages.update', 'nonexistent'),
            ['enabled' => false],
        );

        $response->assertStatus(404);
    }

    public function test_enabled_field_is_required(): void
    {
        $response = $this->actingAs($this->admin)->patch(
            route('admin.packages.update', 'payments'),
            [],
        );

        $response->assertSessionHasErrors('enabled');
    }

    public function test_enabled_field_must_be_boolean(): void
    {
        $response = $this->actingAs($this->admin)->patch(
            route('admin.packages.update', 'payments'),
            ['enabled' => 'not-a-boolean'],
        );

        $response->assertSessionHasErrors('enabled');
    }
}
