<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Models\Permission;
use App\Models\Role;
use App\Repositories\PermissionRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PermissionRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private PermissionRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new PermissionRepository();
    }

    public function test_find_by_id_returns_permission(): void
    {
        $permission = Permission::create(['name' => 'edit-posts']);

        $found = $this->repository->findById($permission->id);

        $this->assertNotNull($found);
        $this->assertEquals('edit-posts', $found->name);
    }

    public function test_find_by_id_returns_null_for_nonexistent(): void
    {
        $found = $this->repository->findById(99999);

        $this->assertNull($found);
    }

    public function test_get_all_with_roles_returns_permissions(): void
    {
        $permission = Permission::create(['name' => 'edit-posts']);
        $role = Role::create(['name' => 'editor']);
        $role->givePermissionTo($permission);

        $permissions = $this->repository->getAllWithRoles();

        $this->assertCount(1, $permissions);
        $this->assertTrue($permissions->first()->relationLoaded('roles'));
        $this->assertCount(1, $permissions->first()->roles);
    }

    public function test_get_all_with_roles_filters_by_name(): void
    {
        Permission::create(['name' => 'edit-posts', 'group_name' => 'Posts']);
        Permission::create(['name' => 'manage-users', 'group_name' => 'Users']);

        $permissions = $this->repository->getAllWithRoles('edit');

        $this->assertCount(1, $permissions);
        $this->assertEquals('edit-posts', $permissions->first()->name);
    }

    public function test_get_all_with_roles_filters_by_group_name(): void
    {
        Permission::create(['name' => 'edit-posts', 'group_name' => 'Posts']);
        Permission::create(['name' => 'manage-users', 'group_name' => 'Users']);

        $permissions = $this->repository->getAllWithRoles('Users');

        $this->assertCount(1, $permissions);
        $this->assertEquals('manage-users', $permissions->first()->name);
    }

    public function test_create_permission(): void
    {
        $permission = $this->repository->create([
            'name' => 'new-perm',
            'group_name' => 'Testing',
        ]);

        $this->assertEquals('new-perm', $permission->name);
        $this->assertDatabaseHas('permissions', ['name' => 'new-perm']);
    }

    public function test_delete_permission(): void
    {
        $permission = Permission::create(['name' => 'deletable']);

        $result = $this->repository->delete($permission->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('permissions', ['name' => 'deletable']);
    }
}
