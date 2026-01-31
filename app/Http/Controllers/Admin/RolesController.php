<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Constants\RoleNames;
use App\Contracts\Services\RoleServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoleRequest;
use App\Http\Requests\Admin\UpdateRoleRequest;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use InvalidArgumentException;

final class RolesController extends Controller
{
    /**
     * Create a new admin roles controller instance.
     */
    public function __construct(private RoleServiceInterface $roleService)
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
     * Display a listing of roles.
     *
     * @param  Request  $request  The incoming request
     * @return Response The Inertia response with roles page data
     */
    public function index(Request $request): Response
    {
        $this->authorizeAdmin();

        $search = $request->filled('search') ? $request->get('search') : null;

        return Inertia::render('admin/Roles/Index', [
            'roles' => $this->roleService->getAll($search),
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

        return Inertia::render('admin/Roles/Create', [
            'permissions' => $this->roleService->getAllPermissions(),
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
     *
     * @param  Role  $role  The role to edit
     * @return Response The Inertia response with edit form
     */
    public function edit(Role $role): Response
    {
        $this->authorizeAdmin();

        return Inertia::render('admin/Roles/Edit', [
            'role' => $this->roleService->getForEdit($role),
            'permissions' => $this->roleService->getAllPermissions(),
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
     *
     * @param  Role  $role  The role to delete
     * @return RedirectResponse Redirect to roles index page
     */
    public function destroy(Role $role): RedirectResponse
    {
        $this->authorizeAdmin();

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
}
