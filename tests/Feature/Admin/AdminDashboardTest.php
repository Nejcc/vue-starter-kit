<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Constants\RoleNames;
use App\Models\AuditLog;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AdminDashboardTest extends TestCase
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

    public function test_guests_cannot_access_admin_dashboard(): void
    {
        $response = $this->get(route('admin.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_regular_users_cannot_access_admin_dashboard(): void
    {
        $user = User::factory()->create();
        $userRole = Role::create(['name' => RoleNames::USER]);
        $user->assignRole($userRole);

        $response = $this->actingAs($user)->get(route('admin.index'));

        $response->assertStatus(403);
    }

    public function test_admin_dashboard_renders_with_stats(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('admin/Dashboard')
            ->has('stats')
            ->has('stats.totalUsers')
            ->has('stats.verifiedUsers')
            ->has('stats.totalRoles')
            ->has('stats.totalPermissions')
            ->has('recentUsers')
            ->has('recentActivity')
        );
    }

    public function test_admin_dashboard_returns_accurate_user_count(): void
    {
        User::factory()->count(5)->create();

        $response = $this->actingAs($this->admin)->get(route('admin.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('stats.totalUsers', 6) // 5 + admin
        );
    }

    public function test_admin_dashboard_returns_verified_user_count(): void
    {
        User::factory()->count(3)->create(['email_verified_at' => now()]);
        User::factory()->count(2)->create(['email_verified_at' => null]);

        $response = $this->actingAs($this->admin)->get(route('admin.index'));

        $response->assertStatus(200);
        // admin + 3 verified factory users = 4 verified
        $response->assertInertia(fn ($page) => $page
            ->where('stats.verifiedUsers', 4)
        );
    }

    public function test_admin_dashboard_returns_role_and_permission_counts(): void
    {
        Role::create(['name' => RoleNames::ADMIN]);
        Permission::create(['name' => 'edit-posts']);
        Permission::create(['name' => 'delete-posts']);

        $response = $this->actingAs($this->admin)->get(route('admin.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('stats.totalRoles', 2) // super-admin + admin
            ->where('stats.totalPermissions', 2)
        );
    }

    public function test_admin_dashboard_returns_recent_users(): void
    {
        User::factory()->count(10)->create();

        $response = $this->actingAs($this->admin)->get(route('admin.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->has('recentUsers', 5) // limited to 5
        );
    }

    public function test_admin_dashboard_returns_recent_activity(): void
    {
        for ($i = 0; $i < 15; $i++) {
            AuditLog::log("test.event.{$i}", null, null, null, $this->admin->id);
        }

        $response = $this->actingAs($this->admin)->get(route('admin.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->has('recentActivity', 10) // limited to 10
        );
    }

    public function test_admin_dashboard_empty_activity(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->has('recentActivity', 0)
        );
    }
}
