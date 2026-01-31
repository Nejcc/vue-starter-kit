<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Constants\RoleNames;
use App\Contracts\Services\PermissionServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePermissionRequest;
use App\Http\Requests\Admin\UpdatePermissionRequest;
use App\Models\Permission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class PermissionsController extends Controller
{
    /**
     * Create a new admin permissions controller instance.
     */
    public function __construct(private PermissionServiceInterface $permissionService)
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

        $search = $request->filled('search') ? $request->get('search') : null;

        return Inertia::render('admin/Permissions/Index', [
            'permissions' => $this->permissionService->getAll($search)->values(),
            'groupedPermissions' => $this->permissionService->getGrouped($search),
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

        $this->permissionService->create($request->validated());

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
            'permission' => $this->permissionService->getForEdit($permission),
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

        $this->permissionService->update($permission, $request->validated());

        return redirect()->route('admin.permissions.index')->with('status', 'Permission updated successfully.');
    }
}
