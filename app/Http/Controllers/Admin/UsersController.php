<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Contracts\Services\RoleServiceInterface;
use App\Contracts\Services\UserServiceInterface;
use App\Exceptions\UserException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\SyncUserPermissionsRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
                'suspended_at' => $user->suspended_at?->toIso8601String(),
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
                'suspended_at' => $user->suspended_at?->toIso8601String(),
                'suspended_reason' => $user->suspended_reason,
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
        } catch (UserException $e) {
            return redirect()->route('admin.users.index')->with('error', $e->getMessage());
        }

        return redirect()->route('admin.users.index')->with('status', 'User deleted successfully.');
    }

    public function export(): StreamedResponse
    {
        $users = $this->userService->getAllForExport();

        return response()->streamDownload(function () use ($users): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Name', 'Email', 'Email Verified', 'Roles', 'Created At']);

            foreach ($users as $user) {
                fputcsv($handle, [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->email_verified_at?->toDateTimeString() ?? 'Not verified',
                    $user->roles->pluck('name')->implode(', '),
                    $user->created_at->toDateTimeString(),
                ]);
            }

            fclose($handle);
        }, 'users-' . now()->format('Y-m-d') . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
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

    public function suspend(Request $request, User $user): RedirectResponse
    {
        try {
            $this->userService->suspend($user->id, $request->input('reason'));
        } catch (UserException $e) {
            return redirect()->back()->withErrors(['suspension' => $e->getMessage()]);
        }

        return redirect()->back()->with('status', 'User suspended successfully.');
    }

    public function unsuspend(User $user): RedirectResponse
    {
        $this->userService->unsuspend($user->id);

        return redirect()->back()->with('status', 'User unsuspended successfully.');
    }
}
