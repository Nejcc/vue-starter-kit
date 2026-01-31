<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Permission;
use App\Models\Role;
use App\Services\PermissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PermissionServiceTest extends TestCase
{
    use RefreshDatabase;

    private PermissionService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new PermissionService();
    }

    public function test_get_all_returns_permissions_with_metadata(): void
    {
        $permission = Permission::create(['name' => 'edit-posts', 'group_name' => 'Posts']);
        $role = Role::create(['name' => 'editor']);
        $role->givePermissionTo($permission);

        $result = $this->service->getAll();

        $this->assertCount(1, $result);
        $this->assertEquals('edit-posts', $result->first()['name']);
        $this->assertEquals('Posts', $result->first()['group_name']);
        $this->assertEquals(1, $result->first()['roles_count']);
        $this->assertContains('editor', $result->first()['roles']->toArray());
    }

    public function test_get_all_with_search_filters_by_name(): void
    {
        Permission::create(['name' => 'edit-posts', 'group_name' => 'Posts']);
        Permission::create(['name' => 'manage-users', 'group_name' => 'Users']);

        $result = $this->service->getAll('edit');

        $this->assertCount(1, $result);
        $this->assertEquals('edit-posts', $result->first()['name']);
    }

    public function test_get_all_with_search_filters_by_group_name(): void
    {
        Permission::create(['name' => 'edit-posts', 'group_name' => 'Posts']);
        Permission::create(['name' => 'manage-users', 'group_name' => 'Users']);

        $result = $this->service->getAll('Users');

        $this->assertCount(1, $result);
        $this->assertEquals('manage-users', $result->first()['name']);
    }

    public function test_get_grouped_organizes_by_group_name(): void
    {
        Permission::create(['name' => 'edit-posts', 'group_name' => 'Posts']);
        Permission::create(['name' => 'delete-posts', 'group_name' => 'Posts']);
        Permission::create(['name' => 'manage-users', 'group_name' => 'Users']);

        $result = $this->service->getGrouped();

        $this->assertCount(2, $result);
        $this->assertCount(2, $result['Posts']);
        $this->assertCount(1, $result['Users']);
    }

    public function test_create_permission(): void
    {
        $permission = $this->service->create([
            'name' => 'new-permission',
            'group_name' => 'Testing',
        ]);

        $this->assertEquals('new-permission', $permission->name);
        $this->assertEquals('Testing', $permission->group_name);
        $this->assertDatabaseHas('permissions', ['name' => 'new-permission']);
    }

    public function test_create_permission_without_group(): void
    {
        $permission = $this->service->create(['name' => 'ungrouped']);

        $this->assertNull($permission->group_name);
    }

    public function test_update_permission(): void
    {
        $permission = Permission::create(['name' => 'old-name', 'group_name' => 'Old']);

        $updated = $this->service->update($permission, [
            'name' => 'new-name',
            'group_name' => 'New',
        ]);

        $this->assertEquals('new-name', $updated->name);
        $this->assertEquals('New', $updated->group_name);
    }

    public function test_update_permission_can_clear_group(): void
    {
        $permission = Permission::create(['name' => 'test', 'group_name' => 'Group']);

        $updated = $this->service->update($permission, [
            'name' => 'test',
            'group_name' => null,
        ]);

        $this->assertNull($updated->group_name);
    }

    public function test_get_for_edit_returns_formatted_data(): void
    {
        $permission = Permission::create(['name' => 'edit-posts', 'group_name' => 'Posts']);

        $data = $this->service->getForEdit($permission);

        $this->assertEquals($permission->id, $data['id']);
        $this->assertEquals('edit-posts', $data['name']);
        $this->assertEquals('Posts', $data['group_name']);
    }
}
