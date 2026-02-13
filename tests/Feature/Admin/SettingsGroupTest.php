<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Constants\RoleNames;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LaravelPlus\GlobalSettings\Enums\SettingGroup;
use LaravelPlus\GlobalSettings\Models\Setting;
use Tests\TestCase;

final class SettingsGroupTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Create an admin user with super-admin role.
     */
    private function createAdmin(): User
    {
        $admin = User::factory()->create();
        $role = Role::firstOrCreate(['name' => RoleNames::SUPER_ADMIN]);
        $admin->assignRole($role);

        return $admin;
    }

    /**
     * Create a regular user.
     */
    private function createUser(): User
    {
        $user = User::factory()->create();
        $role = Role::firstOrCreate(['name' => RoleNames::USER]);
        $user->assignRole($role);

        return $user;
    }

    /**
     * Create a test setting.
     *
     * @param  array<string, mixed>  $overrides
     */
    private function createSetting(array $overrides = []): Setting
    {
        return Setting::create(array_merge([
            'key' => 'test_setting_'.fake()->unique()->word(),
            'value' => 'test_value',
            'field_type' => 'input',
            'label' => 'Test Setting',
            'description' => 'A test setting',
            'role' => 'user',
            'group' => null,
        ], $overrides));
    }

    // ========================================
    // ACCESS CONTROL TESTS
    // ========================================

    /**
     * Test that guests cannot access settings index.
     */
    public function test_guests_cannot_access_settings_index(): void
    {
        $response = $this->get(route('admin.settings.index'));

        $response->assertRedirect(route('login'));
    }

    /**
     * Test that regular users cannot access settings index.
     */
    public function test_regular_users_cannot_access_settings_index(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->get(route('admin.settings.index'));

        $response->assertStatus(403);
    }

    /**
     * Test that guests cannot access settings group page.
     */
    public function test_guests_cannot_access_settings_group(): void
    {
        $response = $this->get(route('admin.settings.group', 'general'));

        $response->assertRedirect(route('login'));
    }

    /**
     * Test that regular users cannot access settings group page.
     */
    public function test_regular_users_cannot_access_settings_group(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->get(route('admin.settings.group', 'general'));

        $response->assertStatus(403);
    }

    // ========================================
    // INDEX TESTS
    // ========================================

    /**
     * Test that admin can view settings index with paginated response.
     */
    public function test_admin_can_view_paginated_settings(): void
    {
        $admin = $this->createAdmin();
        $this->createSetting(['group' => 'general']);

        $response = $this->actingAs($admin)->get(route('admin.settings.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('admin/Settings')
            ->has('settings.data')
            ->has('settings.current_page')
            ->has('settings.last_page')
            ->has('groups')
            ->has('filters')
        );
    }

    /**
     * Test that settings response includes group field.
     */
    public function test_settings_response_includes_group_field(): void
    {
        $admin = $this->createAdmin();
        $this->createSetting(['key' => 'grouped_setting', 'group' => 'general']);

        $response = $this->actingAs($admin)->get(route('admin.settings.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('admin/Settings')
            ->where('settings.data.0.group', 'general')
        );
    }

    /**
     * Test that groups prop contains all enum cases.
     */
    public function test_groups_prop_contains_all_enum_cases(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('admin.settings.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->has('groups', count(SettingGroup::cases()))
            ->where('groups.0.value', 'general')
            ->where('groups.0.label', 'General')
        );
    }

    /**
     * Test that search works on index.
     */
    public function test_search_works_on_settings_index(): void
    {
        $admin = $this->createAdmin();
        $this->createSetting(['key' => 'findable_setting', 'label' => 'Findable Setting']);
        $this->createSetting(['key' => 'other_setting', 'label' => 'Other Setting']);

        $response = $this->actingAs($admin)->get(route('admin.settings.index', ['search' => 'findable']));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->has('settings.data', 1)
            ->where('settings.data.0.key', 'findable_setting')
            ->where('filters.search', 'findable')
        );
    }

    // ========================================
    // GROUP VIEW TESTS
    // ========================================

    /**
     * Test that admin can view settings by group.
     */
    public function test_admin_can_view_settings_by_group(): void
    {
        $admin = $this->createAdmin();
        $this->createSetting(['key' => 'general_setting', 'group' => 'general']);
        $this->createSetting(['key' => 'auth_setting', 'group' => 'authentication']);

        $response = $this->actingAs($admin)->get(route('admin.settings.group', 'general'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('admin/Settings')
            ->has('settings.data', 1)
            ->where('settings.data.0.key', 'general_setting')
            ->has('currentGroup')
            ->where('currentGroup.value', 'general')
            ->where('currentGroup.label', 'General')
        );
    }

    /**
     * Test that group view returns only matching group settings.
     */
    public function test_group_view_returns_only_matching_settings(): void
    {
        $admin = $this->createAdmin();
        $this->createSetting(['key' => 'sec1', 'group' => 'security']);
        $this->createSetting(['key' => 'sec2', 'group' => 'security']);
        $this->createSetting(['key' => 'gen1', 'group' => 'general']);
        $this->createSetting(['key' => 'ungrouped']);

        $response = $this->actingAs($admin)->get(route('admin.settings.group', 'security'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->has('settings.data', 2)
        );
    }

    /**
     * Test that invalid group returns 404.
     */
    public function test_invalid_group_returns_404(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('admin.settings.group', 'nonexistent'));

        $response->assertStatus(404);
    }

    /**
     * Test that search works within group context.
     */
    public function test_search_works_within_group_context(): void
    {
        $admin = $this->createAdmin();
        $this->createSetting(['key' => 'general_findable', 'label' => 'Findable', 'group' => 'general']);
        $this->createSetting(['key' => 'general_other', 'label' => 'Other', 'group' => 'general']);
        $this->createSetting(['key' => 'auth_findable', 'label' => 'Findable', 'group' => 'authentication']);

        $response = $this->actingAs($admin)->get(route('admin.settings.group', ['group' => 'general', 'search' => 'findable']));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->has('settings.data', 1)
            ->where('settings.data.0.key', 'general_findable')
        );
    }

    // ========================================
    // CREATE TESTS
    // ========================================

    /**
     * Test that create page receives groups prop.
     */
    public function test_create_page_receives_groups_prop(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('admin.settings.create'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('admin/Settings/Create')
            ->has('groups', count(SettingGroup::cases()))
        );
    }

    /**
     * Test that admin can create a setting with a group.
     */
    public function test_admin_can_create_setting_with_group(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post(route('admin.settings.store'), [
            'key' => 'new_grouped_setting',
            'value' => 'test',
            'field_type' => 'input',
            'label' => 'New Grouped Setting',
            'role' => 'user',
            'group' => 'general',
        ]);

        $response->assertRedirect(route('admin.settings.index'));

        $this->assertDatabaseHas('settings', [
            'key' => 'new_grouped_setting',
            'group' => 'general',
        ]);
    }

    /**
     * Test that group is optional when creating a setting.
     */
    public function test_group_is_optional_when_creating(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post(route('admin.settings.store'), [
            'key' => 'no_group_setting',
            'value' => 'test',
            'field_type' => 'input',
            'label' => 'No Group Setting',
            'role' => 'user',
        ]);

        $response->assertRedirect(route('admin.settings.index'));

        $this->assertDatabaseHas('settings', [
            'key' => 'no_group_setting',
            'group' => null,
        ]);
    }

    /**
     * Test that invalid group value fails validation on create.
     */
    public function test_invalid_group_fails_validation_on_create(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post(route('admin.settings.store'), [
            'key' => 'bad_group_setting',
            'value' => 'test',
            'field_type' => 'input',
            'label' => 'Bad Group Setting',
            'role' => 'user',
            'group' => 'invalid_group',
        ]);

        $response->assertSessionHasErrors(['group']);
    }

    // ========================================
    // EDIT / UPDATE TESTS
    // ========================================

    /**
     * Test that edit page receives groups prop and setting has group.
     */
    public function test_edit_page_receives_groups_prop(): void
    {
        $admin = $this->createAdmin();
        $setting = $this->createSetting(['key' => 'editable_setting', 'group' => 'security']);

        $response = $this->actingAs($admin)->get(route('admin.settings.edit', $setting));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('admin/Settings/Edit')
            ->has('groups', count(SettingGroup::cases()))
            ->where('setting.group', 'security')
        );
    }

    /**
     * Test that admin can update a setting's group.
     */
    public function test_admin_can_update_setting_group(): void
    {
        $admin = $this->createAdmin();
        $setting = $this->createSetting(['key' => 'update_group_setting', 'group' => 'general']);

        $response = $this->actingAs($admin)->put(route('admin.settings.update', $setting), [
            'key' => 'update_group_setting',
            'value' => 'test',
            'field_type' => 'input',
            'label' => 'Updated Setting',
            'role' => 'user',
            'group' => 'security',
        ]);

        $response->assertRedirect(route('admin.settings.index'));

        $this->assertDatabaseHas('settings', [
            'key' => 'update_group_setting',
            'group' => 'security',
        ]);
    }

    /**
     * Test that invalid group value fails validation on update.
     */
    public function test_invalid_group_fails_validation_on_update(): void
    {
        $admin = $this->createAdmin();
        $setting = $this->createSetting(['key' => 'validate_group_setting']);

        $response = $this->actingAs($admin)->put(route('admin.settings.update', $setting), [
            'key' => 'validate_group_setting',
            'value' => 'test',
            'field_type' => 'input',
            'label' => 'Test',
            'role' => 'user',
            'group' => 'nonexistent_group',
        ]);

        $response->assertSessionHasErrors(['group']);
    }

    // ========================================
    // PAGINATION TESTS
    // ========================================

    /**
     * Test that pagination works correctly on index.
     */
    public function test_pagination_works_on_index(): void
    {
        $admin = $this->createAdmin();

        for ($i = 0; $i < 20; $i++) {
            $this->createSetting(['key' => "paginate_setting_{$i}"]);
        }

        $response = $this->actingAs($admin)->get(route('admin.settings.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->has('settings.data', 15)
            ->where('settings.current_page', 1)
            ->where('settings.last_page', 2)
            ->where('settings.total', 20)
        );
    }

    /**
     * Test that pagination page 2 works.
     */
    public function test_pagination_page_two_works(): void
    {
        $admin = $this->createAdmin();

        for ($i = 0; $i < 20; $i++) {
            $this->createSetting(['key' => "page2_setting_{$i}"]);
        }

        $response = $this->actingAs($admin)->get(route('admin.settings.index', ['page' => 2]));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->has('settings.data', 5)
            ->where('settings.current_page', 2)
        );
    }
}
