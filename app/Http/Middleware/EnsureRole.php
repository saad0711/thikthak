<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Only lets users with one of the given roles through. Used in routes as
 * `role:admin` or `role:customer,admin`. Anyone else gets 403 Forbidden.
 */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($user->role->value, $roles, true)) {
            abort(403, 'You don\'t have access to this page.');
        }

        return $next($request);
    }
}
