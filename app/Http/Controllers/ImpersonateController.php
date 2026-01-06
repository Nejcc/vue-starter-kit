<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Constants\RoleNames;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

final class ImpersonateController extends Controller
{
    /**
     * Create a new impersonate controller instance.
     */
    public function __construct(
        protected UserService $userService
    ) {
        $this->middleware('auth');
    }

    /**
     * Display a listing of users for impersonation.
     */
    public function index(Request $request): Response|\Illuminate\Http\JsonResponse
    {
        $this->authorizeImpersonate();

        $search = $request->input('search', '');

        $users = $search
            ? $this->userService->search($search, 50)
            : $this->userService->getAllForImpersonation(Auth::id());

        $usersData = $users->map(fn ($user) => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'initials' => $user->initials,
        ]);

        // If this is a JSON request (for modal), return JSON
        if ($request->wantsJson() || $request->has('partial')) {
            return response()->json([
                'users' => $usersData,
                'search' => $search,
            ]);
        }

        // Otherwise, render the full page
        return Inertia::render('Impersonate/Index', [
            'users' => $usersData,
            'search' => $search,
        ]);
    }

    /**
     * Impersonate a user.
     */
    public function store(Request $request): \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
    {
        $this->authorizeImpersonate();

        $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $user = $this->userService->findById($request->input('user_id'));

        if (!$user) {
            return back()->withErrors(['user_id' => 'User not found.']);
        }

        // Prevent impersonating yourself
        if ($user->id === Auth::id()) {
            return back()->withErrors(['user_id' => 'You cannot impersonate yourself.']);
        }

        // Store the original user ID in session
        session()->put('impersonator_id', Auth::id());

        // Log in as the impersonated user
        Auth::login($user);
        $request->session()->regenerate();

        // Use Inertia location for full page reload to update all shared props
        return Inertia::location(route('dashboard'));
    }

    /**
     * Stop impersonating and return to original user.
     */
    public function destroy(Request $request): \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
    {
        $impersonatorId = session()->pull('impersonator_id');

        if (!$impersonatorId) {
            return redirect()->route('dashboard');
        }

        $impersonator = $this->userService->findById($impersonatorId);

        if (!$impersonator) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login');
        }

        Auth::login($impersonator);
        $request->session()->regenerate();

        // Use Inertia location for full page reload to update all shared props
        return Inertia::location(route('dashboard'));
    }

    /**
     * Authorize impersonation access.
     */
    private function authorizeImpersonate(): void
    {
        $user = Auth::user();

        if (!$user) {
            abort(403, 'Unauthorized.');
        }

        // Check if user is super-admin or has impersonate permission
        if (!$user->hasRole(RoleNames::SUPER_ADMIN) && !$user->can('impersonate')) {
            abort(403, 'Unauthorized. Impersonation requires super-admin role or impersonate permission.');
        }
    }
}
