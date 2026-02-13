<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Contracts\Services\RoleServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoleRequest;
use App\Http\Requests\Admin\SyncRolePermissionsRequest;
use App\Http\Requests\Admin\UpdateRoleRequest;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use InvalidArgumentException;

final class RolesController extends Controller
{
    public function __construct(private readonly RoleServiceInterface $roleService) {}

    /**
     * Display a listing of roles.
     */
    public function index(Request $request): Response
    {
        $search = $request->filled('search') ? $request->get('search') : null;

        return Inertia::render('admin/Roles/Index', [
            'roles' => $this->roleService->getPaginated($search),
            'status' => $request->session()->get('status'),
            'filters' => [
                'search' => $request->get('search', ''),
            ],
        ]);
    }

    /**
     * Show the form for creating a new role.
     */
    public function create(): Response
    {
        return Inertia::render('admin/Roles/Create', [
            'permissions' => $this->roleService->getAllPermissions(),
        ]);
    }

    /**
     * Store a newly created role.
     */
    public function store(StoreRoleRequest $request): RedirectResponse
    {
        try {
            $this->roleService->create($request->validated());
        } catch (InvalidArgumentException $e) {
            return redirect()->route('admin.roles.create')
                ->withErrors(['name' => $e->getMessage()]);
        }

        return redirect()->route('admin.roles.index')->with('status', 'Role created successfully.');
    }

    /**
     * Show the form for editing a role.
     */
    public function edit(Role $role): Response
    {
        return Inertia::render('admin/Roles/Edit', [
            'role' => $this->roleService->getForEdit($role),
            'permissions' => $this->roleService->getAllPermissions(),
        ]);
    }

    /**
     * Update the specified role.
     */
    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        try {
            $this->roleService->update($role, $request->validated());
        } catch (InvalidArgumentException $e) {
            return redirect()->route('admin.roles.edit', $role)
                ->withErrors(['name' => $e->getMessage()]);
        }

        return redirect()->route('admin.roles.index')->with('status', 'Role updated successfully.');
    }

    /**
     * Remove the specified role.
     */
    public function destroy(Role $role): RedirectResponse
    {
        try {
            $this->roleService->delete($role);
        } catch (InvalidArgumentException $e) {
            if (str_contains($e->getMessage(), 'assigned to')) {
                return redirect()->route('admin.roles.index')
                    ->withErrors(['role_deletion' => $e->getMessage()]);
            }

            return redirect()->route('admin.roles.index')
                ->with('error', $e->getMessage());
        }

        return redirect()->route('admin.roles.index')->with('status', 'Role deleted successfully.');
    }

    public function permissions(Role $role): Response
    {
        return Inertia::render('admin/Roles/Permissions', [
            'role' => $this->roleService->getPermissionsData($role),
            'allPermissions' => $this->roleService->getAllPermissions(),
        ]);
    }

    public function updatePermissions(SyncRolePermissionsRequest $request, Role $role): RedirectResponse
    {
        try {
            $this->roleService->syncPermissions($role, $request->validated());
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('status', 'Permissions updated successfully.');
    }
}
