<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Constants\RoleNames;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoleRequest;
use App\Http\Requests\Admin\UpdateRoleRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class RolesController extends Controller
{
    /**
     * Create a new admin roles controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Check if user has admin or super-admin role.
     */
    private function authorizeAdmin(): void
    {
        $user = auth()->user();

        if (!$user || (!$user->hasRole(RoleNames::SUPER_ADMIN) && !$user->hasRole(RoleNames::ADMIN))) {
            abort(403, 'Unauthorized. Admin access required.');
        }
    }

    /**
     * Check if a role is the special super-admin role.
     */
    private function isSuperAdminRole(Role $role): bool
    {
        return $role->name === RoleNames::SUPER_ADMIN;
    }

    /**
     * Display a listing of roles.
     *
     * @param  Request  $request  The incoming request
     * @return Response The Inertia response with roles page data
     */
    public function index(Request $request): Response
    {
        $this->authorizeAdmin();

        $query = Role::with('permissions');

        // Search functionality
        if ($request->has('search') && $request->filled('search')) {
            $search = $request->get('search');
            $query->where('name', 'like', "%{$search}%");
        }

        $roles = $query->latest()->get();

        return Inertia::render('admin/Roles/Index', [
            'roles' => $roles->map(fn ($role) => [
                'id' => $role->id,
                'name' => $role->name,
                'is_super_admin' => $this->isSuperAdminRole($role),
                'permissions' => $role->permissions->pluck('name'),
                'users_count' => $role->users()->count(),
                'created_at' => $role->created_at,
            ]),
            'status' => $request->session()->get('status'),
            'filters' => [
                'search' => $request->get('search', ''),
            ],
        ]);
    }

    /**
     * Show the form for creating a new role.
     *
     * @return Response The Inertia response with create form
     */
    public function create(): Response
    {
        $this->authorizeAdmin();

        $permissions = Permission::all()->pluck('name');

        return Inertia::render('admin/Roles/Create', [
            'permissions' => $permissions,
        ]);
    }

    /**
     * Store a newly created role.
     *
     * @param  StoreRoleRequest  $request  The validated request
     * @return RedirectResponse Redirect to roles index page
     */
    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $this->authorizeAdmin();

        // Prevent creating super-admin role
        if ($request->validated()['name'] === RoleNames::SUPER_ADMIN) {
            return redirect()->route('admin.roles.create')
                ->withErrors(['name' => 'The super-admin role cannot be created. It is a system role.']);
        }

        $role = Role::create([
            'name' => $request->validated()['name'],
        ]);

        if ($request->has('permissions') && is_array($request->validated()['permissions'])) {
            $role->givePermissionTo($request->validated()['permissions']);
        }

        return redirect()->route('admin.roles.index')->with('status', 'Role created successfully.');
    }

    /**
     * Show the form for editing a role.
     *
     * @param  Role  $role  The role to edit
     * @return Response The Inertia response with edit form
     */
    public function edit(Role $role): Response
    {
        $this->authorizeAdmin();

        $permissions = Permission::all()->pluck('name');

        return Inertia::render('admin/Roles/Edit', [
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'is_super_admin' => $this->isSuperAdminRole($role),
                'permissions' => $role->permissions->pluck('name'),
            ],
            'permissions' => $permissions,
        ]);
    }

    /**
     * Update the specified role.
     *
     * @param  UpdateRoleRequest  $request  The validated request
     * @param  Role  $role  The role to update
     * @return RedirectResponse Redirect to roles index page
     */
    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $this->authorizeAdmin();

        // Prevent renaming super-admin role
        if ($this->isSuperAdminRole($role) && $request->validated()['name'] !== RoleNames::SUPER_ADMIN) {
            return redirect()->route('admin.roles.edit', $role)
                ->withErrors(['name' => 'The super-admin role name cannot be changed.']);
        }

        // Prevent changing another role to super-admin
        if (!$this->isSuperAdminRole($role) && $request->validated()['name'] === RoleNames::SUPER_ADMIN) {
            return redirect()->route('admin.roles.edit', $role)
                ->withErrors(['name' => 'The super-admin role name cannot be used. It is a system role.']);
        }

        $role->update([
            'name' => $request->validated()['name'],
        ]);

        // Update permissions
        if ($request->has('permissions') && is_array($request->validated()['permissions'])) {
            $role->syncPermissions($request->validated()['permissions']);
        } else {
            $role->syncPermissions([]);
        }

        return redirect()->route('admin.roles.index')->with('status', 'Role updated successfully.');
    }

    /**
     * Remove the specified role.
     *
     * @param  Role  $role  The role to delete
     * @return RedirectResponse Redirect to roles index page
     */
    public function destroy(Role $role): RedirectResponse
    {
        $this->authorizeAdmin();

        // Prevent deletion of super-admin role
        if ($this->isSuperAdminRole($role)) {
            return redirect()->route('admin.roles.index')
                ->with('error', 'The super-admin role cannot be deleted. It is a system role with all permissions.');
        }

        $role->delete();

        return redirect()->route('admin.roles.index')->with('status', 'Role deleted successfully.');
    }
}
