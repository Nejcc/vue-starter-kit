<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Constants\RoleNames;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePermissionRequest;
use App\Http\Requests\Admin\UpdatePermissionRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;

final class PermissionsController extends Controller
{
    /**
     * Create a new admin permissions controller instance.
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
     * Display a listing of permissions.
     *
     * @param  Request  $request  The incoming request
     * @return Response The Inertia response with permissions page data
     */
    public function index(Request $request): Response
    {
        $this->authorizeAdmin();

        $query = Permission::with('roles');

        // Search functionality
        if ($request->has('search') && $request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('group_name', 'like', "%{$search}%");
            });
        }

        $permissions = $query->latest()->get();

        $permissionsData = $permissions->map(fn ($permission) => [
            'id' => $permission->id,
            'name' => $permission->name,
            'group_name' => $permission->group_name,
            'roles' => $permission->roles->pluck('name'),
            'roles_count' => $permission->roles()->count(),
            'created_at' => $permission->created_at,
        ]);

        // Group permissions by group_name
        $groupedPermissions = $permissionsData->groupBy('group_name')->map(fn ($group) => $group->values());

        return Inertia::render('admin/Permissions/Index', [
            'permissions' => $permissionsData->values(),
            'groupedPermissions' => $groupedPermissions,
            'status' => $request->session()->get('status'),
            'filters' => [
                'search' => $request->get('search', ''),
            ],
        ]);
    }

    /**
     * Show the form for creating a new permission.
     *
     * @return Response The Inertia response with create form
     */
    public function create(): Response
    {
        $this->authorizeAdmin();

        return Inertia::render('admin/Permissions/Create');
    }

    /**
     * Store a newly created permission.
     *
     * @param  StorePermissionRequest  $request  The validated request
     * @return RedirectResponse Redirect to permissions index page
     */
    public function store(StorePermissionRequest $request): RedirectResponse
    {
        $this->authorizeAdmin();

        Permission::create([
            'name' => $request->validated()['name'],
            'group_name' => $request->validated()['group_name'] ?? null,
        ]);

        return redirect()->route('admin.permissions.index')->with('status', 'Permission created successfully.');
    }

    /**
     * Show the form for editing a permission.
     *
     * @param  Permission  $permission  The permission to edit
     * @return Response The Inertia response with edit form
     */
    public function edit(Permission $permission): Response
    {
        $this->authorizeAdmin();

        return Inertia::render('admin/Permissions/Edit', [
            'permission' => [
                'id' => $permission->id,
                'name' => $permission->name,
                'group_name' => $permission->group_name,
            ],
        ]);
    }

    /**
     * Update the specified permission.
     *
     * @param  UpdatePermissionRequest  $request  The validated request
     * @param  Permission  $permission  The permission to update
     * @return RedirectResponse Redirect to permissions index page
     */
    public function update(UpdatePermissionRequest $request, Permission $permission): RedirectResponse
    {
        $this->authorizeAdmin();

        $permission->update([
            'name' => $request->validated()['name'],
            'group_name' => $request->validated()['group_name'] ?? null,
        ]);

        return redirect()->route('admin.permissions.index')->with('status', 'Permission updated successfully.');
    }
}
