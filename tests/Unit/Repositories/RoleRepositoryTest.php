<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Models\Permission;
use App\Models\Role;
use App\Repositories\RoleRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class RoleRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private RoleRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new RoleRepository();
    }

    public function test_find_by_id_returns_role(): void
    {
        $role = Role::create(['name' => 'editor']);

        $found = $this->repository->findById($role->id);

        $this->assertNotNull($found);
        $this->assertEquals('editor', $found->name);
    }

    public function test_find_by_id_returns_null_for_nonexistent(): void
    {
        $found = $this->repository->findById(99999);

        $this->assertNull($found);
    }

    public function test_get_all_with_permissions_returns_roles(): void
    {
        $role = Role::create(['name' => 'editor']);
        $permission = Permission::create(['name' => 'edit-posts']);
        $role->givePermissionTo($permission);

        $roles = $this->repository->getAllWithPermissions();

        $this->assertCount(1, $roles);
        $this->assertTrue($roles->first()->relationLoaded('permissions'));
        $this->assertCount(1, $roles->first()->permissions);
    }

    public function test_get_all_with_permissions_filters_by_search(): void
    {
        Role::create(['name' => 'editor']);
        Role::create(['name' => 'moderator']);

        $roles = $this->repository->getAllWithPermissions('editor');

        $this->assertCount(1, $roles);
        $this->assertEquals('editor', $roles->first()->name);
    }

    public function test_get_all_with_permissions_returns_all_without_search(): void
    {
        Role::create(['name' => 'editor']);
        Role::create(['name' => 'moderator']);

        $roles = $this->repository->getAllWithPermissions();

        $this->assertCount(2, $roles);
    }

    public function test_get_all_permission_names_returns_names(): void
    {
        Permission::create(['name' => 'perm-a']);
        Permission::create(['name' => 'perm-b']);

        $names = $this->repository->getAllPermissionNames();

        $this->assertCount(2, $names);
        $this->assertContains('perm-a', $names->toArray());
        $this->assertContains('perm-b', $names->toArray());
    }

    public function test_create_role(): void
    {
        $role = $this->repository->create(['name' => 'new-role']);

        $this->assertEquals('new-role', $role->name);
        $this->assertDatabaseHas('roles', ['name' => 'new-role']);
    }

    public function test_delete_role(): void
    {
        $role = Role::create(['name' => 'deletable']);

        $result = $this->repository->delete($role->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('roles', ['name' => 'deletable']);
    }
}
