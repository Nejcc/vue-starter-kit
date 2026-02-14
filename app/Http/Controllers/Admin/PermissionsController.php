<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Contracts\Services\PermissionServiceInterface;
use App\Exceptions\PermissionException;
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
    public function __construct(private readonly PermissionServiceInterface $permissionService) {}

    /**
     * Display a listing of permissions.
     */
    public function index(Request $request): Response
    {
        $search = $request->filled('search') ? $request->get('search') : null;
        $group = $request->filled('group') ? $request->get('group') : null;

        return Inertia::render('admin/Permissions/Index', [
            'permissions' => $this->permissionService->getPaginated($search, 15, $group),
            'groups' => $this->permissionService->getGroupNames(),
            'status' => $request->session()->get('status'),
            'filters' => [
                'search' => $request->get('search', ''),
                'group' => $request->get('group', ''),
            ],
        ]);
    }

    /**
     * Show the form for creating a new permission.
     */
    public function create(): Response
    {
        return Inertia::render('admin/Permissions/Create');
    }

    /**
     * Store a newly created permission.
     */
    public function store(StorePermissionRequest $request): RedirectResponse
    {
        $this->permissionService->create($request->validated());

        return redirect()->route('admin.permissions.index')->with('status', 'Permission created successfully.');
    }

    /**
     * Show the form for editing a permission.
     */
    public function edit(Permission $permission): Response
    {
        return Inertia::render('admin/Permissions/Edit', [
            'permission' => $this->permissionService->getForEdit($permission),
        ]);
    }

    /**
     * Update the specified permission.
     */
    public function update(UpdatePermissionRequest $request, Permission $permission): RedirectResponse
    {
        $this->permissionService->update($permission, $request->validated());

        return redirect()->route('admin.permissions.index')->with('status', 'Permission updated successfully.');
    }

    /**
     * Delete the specified permission.
     */
    public function destroy(Permission $permission): RedirectResponse
    {
        try {
            $this->permissionService->delete($permission);

            return redirect()->route('admin.permissions.index')->with('status', 'Permission deleted successfully.');
        } catch (PermissionException $e) {
            return back()->withErrors(['permission_deletion' => $e->getMessage()]);
        }
    }
}
