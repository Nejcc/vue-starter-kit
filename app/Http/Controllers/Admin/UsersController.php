<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Contracts\Services\RoleServiceInterface;
use App\Contracts\Services\UserServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\SyncUserPermissionsRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use InvalidArgumentException;

final class UsersController extends Controller
{
    public function __construct(
        private readonly UserServiceInterface $userService,
        private readonly RoleServiceInterface $roleService,
    ) {}

    public function index(Request $request): Response
    {
        $users = $this->userService->getAdminPaginated($request->get('search'))
            ->through(fn ($user) => [
                'id' => $user->id,
                'slug' => $user->slug,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified_at' => $user->email_verified_at?->toIso8601String(),
                'roles' => $user->roles->pluck('name')->toArray(),
                'created_at' => $user->created_at->toIso8601String(),
            ]);

        return Inertia::render('admin/Users/Index', [
            'users' => $users,
            'status' => $request->session()->get('status'),
            'filters' => [
                'search' => $request->get('search', ''),
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/Users/Create', [
            'roles' => $this->roleService->getAllRoleNames(),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->userService->adminCreate($request->validated());

        return redirect()->route('admin.users.index')->with('status', 'User created successfully.');
    }

    public function edit(User $user): Response
    {
        return Inertia::render('admin/Users/Edit', [
            'user' => [
                'id' => $user->id,
                'slug' => $user->slug,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified_at' => $user->email_verified_at?->toIso8601String(),
                'roles' => $user->roles->pluck('name')->toArray(),
                'created_at' => $user->created_at->toIso8601String(),
            ],
            'roles' => $this->roleService->getAllRoleNames(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->userService->adminUpdate($user->id, $request->validated());

        return redirect()->route('admin.users.index')->with('status', 'User updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        try {
            $this->userService->adminDelete($user->id);
        } catch (InvalidArgumentException $e) {
            return redirect()->route('admin.users.index')->with('error', $e->getMessage());
        }

        return redirect()->route('admin.users.index')->with('status', 'User deleted successfully.');
    }

    public function permissions(User $user): Response
    {
        return Inertia::render('admin/Users/Permissions', [
            'user' => $this->userService->getPermissionsData($user),
            'allPermissions' => $this->roleService->getAllPermissions(),
        ]);
    }

    public function updatePermissions(SyncUserPermissionsRequest $request, User $user): RedirectResponse
    {
        $this->userService->syncPermissions($user, $request->validated());

        return redirect()->back()->with('status', 'Permissions updated successfully.');
    }
}
