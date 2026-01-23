<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

final class EnsureUserExists
{
    /**
     * Handle an incoming request.
     *
     * Ensures the authenticated user still exists in the database.
     * If the user has been deleted, logs them out and redirects to login.
     * This is particularly important for impersonation scenarios where
     * the impersonated user might be deleted during the session.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $userId = Auth::id();

            // Check if the user exists in the database
            $userExists = User::where('id', $userId)->exists();

            if (!$userExists) {
                // Check if user was being impersonated
                $impersonatorId = session()->get('impersonator_id');

                // Clear session and log out
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                // If there was an impersonator, try to restore them
                if ($impersonatorId) {
                    $impersonator = User::find($impersonatorId);

                    if ($impersonator) {
                        Auth::login($impersonator);
                        session()->forget('impersonator_id');

                        return redirect()
                            ->route('dashboard')
                            ->with('error', 'The impersonated user no longer exists. You have been returned to your account.');
                    }
                }

                // Otherwise, redirect to login
                return redirect()
                    ->route('login')
                    ->with('error', 'Your account no longer exists. Please contact support if you believe this is an error.');
            }
        }

        return $next($request);
    }
}
