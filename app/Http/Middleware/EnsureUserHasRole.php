<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * Checks if the authenticated user has any of the specified roles.
     * Redirects to dashboard with error if user lacks permission.
     *
     * @param  Closure(Request): (Response)  $next
     * @param  string  ...$roles  One or more role names to check (e.g., 'admin', 'super-admin')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!$request->user()) {
            abort(401, 'Unauthorized');
        }

        if (!$request->user()->hasAnyRole($roles)) {
            abort(403, 'You do not have permission to access this resource.');
        }

        return $next($request);
    }
}
