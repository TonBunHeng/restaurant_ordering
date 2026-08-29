<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
                'errors' => ['auth' => ['Please log in to continue.']],
            ], Response::HTTP_UNAUTHORIZED);
        }

        if ($user->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Your account is currently ' . ($user->status ?: 'deactivated') . '.',
                'errors' => ['account' => ['Please contact a system administrator.']],
            ], Response::HTTP_FORBIDDEN);
        }

        if (empty($roles)) {
            return $next($request);
        }

        // super_admin has access to everything
        if ($user->role === 'super_admin') {
            return $next($request);
        }

        if (in_array($user->role, $roles, true)) {
            return $next($request);
        }

        return response()->json([
            'success' => false,
            'message' => 'Unauthorized access. Insufficient permissions.',
            'errors' => ['role' => ['You do not have permission to perform this action.']],
        ], Response::HTTP_FORBIDDEN);
    }
}
