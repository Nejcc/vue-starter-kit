<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Constants\RoleNames;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

final class UsersController extends Controller
{
    /**
     * Create a new admin users controller instance.
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
     * Display a listing of users.
     *
     * @param  Request  $request  The incoming request
     * @return Response The Inertia response with users page data
     */
    public function index(Request $request): Response
    {
        $this->authorizeAdmin();

        $query = User::with('roles');

        // Search functionality
        if ($request->has('search') && $request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(15);

        return Inertia::render('admin/Users/Index', [
            'users' => [
                'data' => $users->map(fn ($user) => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'email_verified_at' => $user->email_verified_at?->toIso8601String(),
                    'roles' => $user->roles->pluck('name')->toArray(),
                    'created_at' => $user->created_at->toIso8601String(),
                ])->toArray(),
                'links' => $users->linkCollection()->toArray(),
                'meta' => [
                    'current_page' => $users->currentPage(),
                    'from' => $users->firstItem(),
                    'last_page' => $users->lastPage(),
                    'per_page' => $users->perPage(),
                    'to' => $users->lastItem(),
                    'total' => $users->total(),
                ],
            ],
            'status' => $request->session()->get('status'),
            'filters' => [
                'search' => $request->get('search', ''),
            ],
        ]);
    }

    /**
     * Show the form for creating a new user.
     *
     * @return Response The Inertia response with create form
     */
    public function create(): Response
    {
        $this->authorizeAdmin();

        $roles = Role::all()->pluck('name');

        return Inertia::render('admin/Users/Create', [
            'roles' => $roles,
        ]);
    }

    /**
     * Store a newly created user.
     *
     * @param  StoreUserRequest  $request  The validated request
     * @return RedirectResponse Redirect to users index page
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->authorizeAdmin();

        $user = User::create([
            'name' => $request->validated()['name'],
            'email' => $request->validated()['email'],
            'password' => Hash::make($request->validated()['password']),
        ]);

        if ($request->has('roles') && is_array($request->validated()['roles'])) {
            $user->assignRole($request->validated()['roles']);
        }

        return redirect()->route('admin.users.index')->with('status', 'User created successfully.');
    }

    /**
     * Show the form for editing the specified user.
     *
     * @param  User  $user  The user to edit
     * @return Response The Inertia response with edit form
     */
    public function edit(User $user): Response
    {
        $this->authorizeAdmin();

        $roles = Role::all()->pluck('name');

        return Inertia::render('admin/Users/Edit', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified_at' => $user->email_verified_at?->toIso8601String(),
                'roles' => $user->roles->pluck('name')->toArray(),
                'created_at' => $user->created_at->toIso8601String(),
            ],
            'roles' => $roles,
        ]);
    }

    /**
     * Update the specified user in storage.
     *
     * @param  UpdateUserRequest  $request  The validated request
     * @param  User  $user  The user to update
     * @return RedirectResponse Redirect to users index page
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->authorizeAdmin();

        $data = [
            'name' => $request->validated()['name'],
            'email' => $request->validated()['email'],
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->validated()['password']);
        }

        $user->update($data);

        // Sync roles
        if ($request->has('roles')) {
            $user->syncRoles($request->validated()['roles'] ?? []);
        }

        return redirect()->route('admin.users.index')->with('status', 'User updated successfully.');
    }

    /**
     * Remove the specified user from storage.
     *
     * @param  User  $user  The user to delete
     * @return RedirectResponse Redirect to users index page
     */
    public function destroy(User $user): RedirectResponse
    {
        $this->authorizeAdmin();

        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('status', 'User deleted successfully.');
    }
}
