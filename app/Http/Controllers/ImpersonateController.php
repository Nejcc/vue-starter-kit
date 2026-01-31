<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\ImpersonationService;
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
        protected ImpersonationService $impersonationService
    ) {
        $this->middleware('auth');

        $this->middleware('throttle:impersonate')->only('store');
    }

    /**
     * Display a listing of users for impersonation.
     */
    public function index(Request $request): Response|\Illuminate\Http\JsonResponse
    {
        $this->authorizeImpersonate();

        $search = $request->input('search', '');

        $users = $search
            ? $this->impersonationService->searchUsers($search, 50)
            : $this->impersonationService->getUsersForImpersonation(Auth::id());

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

        $result = $this->impersonationService->startImpersonation(
            Auth::user(),
            $request->integer('user_id'),
            $request
        );

        if (!$result['success']) {
            return back()->withErrors(['user_id' => $result['error']]);
        }

        // Use Inertia location for full page reload to update all shared props
        return Inertia::location(route('dashboard'));
    }

    /**
     * Stop impersonating and return to original user.
     */
    public function destroy(Request $request): \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
    {
        $result = $this->impersonationService->stopImpersonation($request);

        if (!$result['success']) {
            if (isset($result['logout']) && $result['logout']) {
                return redirect()
                    ->route('login')
                    ->with('error', $result['error']);
            }

            return redirect()->route('dashboard');
        }

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

        if (!$this->impersonationService->canImpersonate($user)) {
            abort(403, 'Unauthorized. Impersonation requires super-admin/admin role or impersonate permission.');
        }
    }
}
