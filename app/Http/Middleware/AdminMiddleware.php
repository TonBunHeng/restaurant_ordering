<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Please log in to access the management portal.');
        }

        $user = auth()->user();

        if ($user->status !== 'active') {
            auth()->logout();
            return redirect()->route('login')->with('error', 'Your account has been deactivated. Please contact support.');
        }

        if (!in_array($user->role, ['staff', 'admin', 'super_admin'], true)) {
            abort(403, 'Unauthorized access. Management privileges required.');
        }

        return $next($request);
    }
}
