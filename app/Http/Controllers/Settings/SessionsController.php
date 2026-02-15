<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Contracts\Services\SessionManagementServiceInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

final class SessionsController extends Controller
{
    public function __construct(
        private readonly SessionManagementServiceInterface $sessionService,
    ) {}

    /**
     * Show the user's active sessions.
     */
    public function index(Request $request): Response
    {
        $sessions = $this->sessionService->getUserSessions(
            $request->user()->id,
            $request->session()->getId(),
        );

        return Inertia::render('settings/Sessions', [
            'sessions' => $sessions,
            'currentSessionId' => $request->session()->getId(),
        ]);
    }

    /**
     * Destroy a specific session.
     */
    public function destroy(Request $request, string $sessionId): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        if (!Hash::check($request->input('password'), $request->user()->password)) {
            throw ValidationException::withMessages([
                'password' => ['The provided password is incorrect.'],
            ]);
        }

        if ($sessionId === $request->session()->getId()) {
            return redirect()->back()->withErrors([
                'session' => 'You cannot revoke your current session. Use logout instead.',
            ]);
        }

        $this->sessionService->revokeSession($sessionId, $request->user()->id);

        return redirect()->back()->with('status', 'Session revoked successfully.');
    }

    /**
     * Destroy all sessions except the current one.
     */
    public function destroyAll(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        if (!Hash::check($request->input('password'), $request->user()->password)) {
            throw ValidationException::withMessages([
                'password' => ['The provided password is incorrect.'],
            ]);
        }

        $this->sessionService->revokeAllOtherSessions(
            $request->user()->id,
            $request->session()->getId(),
        );

        return redirect()->back()->with('status', 'All other sessions have been revoked.');
    }
}
