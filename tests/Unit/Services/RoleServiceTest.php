<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Constants\RoleNames;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\RoleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

final class RoleServiceTest extends TestCase
{
    use RefreshDatabase;

    private RoleService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new RoleService();
    }

    public function test_get_all_returns_roles_with_metadata(): void
    {
        $role = Role::create(['name' => 'editor']);
        $permission = Permission::create(['name' => 'edit-posts']);
        $role->givePermissionTo($permission);

        $result = $this->service->getAll();

        $this->assertCount(1, $result);
        $this->assertEquals('editor', $result->first()['name']);
        $this->assertFalse($result->first()['is_super_admin']);
        $this->assertContains('edit-posts', $result->first()['permissions']->toArray());
    }

    public function test_get_all_with_search_filters_by_name(): void
    {
        Role::create(['name' => 'editor']);
        Role::create(['name' => 'moderator']);

        $result = $this->service->getAll('editor');

        $this->assertCount(1, $result);
        $this->assertEquals('editor', $result->first()['name']);
    }

    public function test_get_all_marks_super_admin(): void
    {
        Role::create(['name' => RoleNames::SUPER_ADMIN]);

        $result = $this->service->getAll();

        $this->assertTrue($result->first()['is_super_admin']);
    }

    public function test_create_role_with_permissions(): void
    {
        Permission::create(['name' => 'edit-posts']);
        Permission::create(['name' => 'delete-posts']);

        $role = $this->service->create([
            'name' => 'editor',
            'permissions' => ['edit-posts', 'delete-posts'],
        ]);

        $this->assertEquals('editor', $role->name);
        $this->assertTrue($role->hasPermissionTo('edit-posts'));
        $this->assertTrue($role->hasPermissionTo('delete-posts'));
    }

    public function test_create_role_without_permissions(): void
    {
        $role = $this->service->create(['name' => 'viewer']);

        $this->assertEquals('viewer', $role->name);
        $this->assertCount(0, $role->permissions);
    }

    public function test_create_super_admin_role_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('super-admin role cannot be created');

        $this->service->create(['name' => RoleNames::SUPER_ADMIN]);
    }

    public function test_update_role_name_and_permissions(): void
    {
        $role = Role::create(['name' => 'old-name']);
        $permission = Permission::create(['name' => 'new-perm']);

        $updated = $this->service->update($role, [
            'name' => 'new-name',
            'permissions' => ['new-perm'],
        ]);

        $this->assertEquals('new-name', $updated->name);
        $this->assertTrue($updated->hasPermissionTo('new-perm'));
    }

    public function test_update_super_admin_name_throws_exception(): void
    {
        $role = Role::create(['name' => RoleNames::SUPER_ADMIN]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('super-admin role name cannot be changed');

        $this->service->update($role, ['name' => 'renamed']);
    }

    public function test_update_role_to_super_admin_name_throws_exception(): void
    {
        $role = Role::create(['name' => 'editor']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('super-admin role name cannot be used');

        $this->service->update($role, ['name' => RoleNames::SUPER_ADMIN]);
    }

    public function test_delete_role(): void
    {
        $role = Role::create(['name' => 'deletable']);

        $result = $this->service->delete($role);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('roles', ['name' => 'deletable']);
    }

    public function test_delete_super_admin_throws_exception(): void
    {
        $role = Role::create(['name' => RoleNames::SUPER_ADMIN]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('super-admin role cannot be deleted');

        $this->service->delete($role);
    }

    public function test_delete_role_with_users_throws_exception(): void
    {
        $role = Role::create(['name' => 'assigned-role']);
        $user = User::factory()->create();
        $user->assignRole($role);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('assigned to 1 user(s)');

        $this->service->delete($role);
    }

    public function test_get_for_edit_returns_formatted_data(): void
    {
        $role = Role::create(['name' => 'editor']);
        $permission = Permission::create(['name' => 'edit-posts']);
        $role->givePermissionTo($permission);

        $data = $this->service->getForEdit($role);

        $this->assertEquals($role->id, $data['id']);
        $this->assertEquals('editor', $data['name']);
        $this->assertFalse($data['is_super_admin']);
        $this->assertContains('edit-posts', $data['permissions']->toArray());
    }

    public function test_get_all_permissions_returns_names(): void
    {
        Permission::create(['name' => 'perm-a']);
        Permission::create(['name' => 'perm-b']);

        $permissions = $this->service->getAllPermissions();

        $this->assertCount(2, $permissions);
        $this->assertContains('perm-a', $permissions->toArray());
        $this->assertContains('perm-b', $permissions->toArray());
    }
}
