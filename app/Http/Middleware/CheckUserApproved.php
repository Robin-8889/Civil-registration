<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserApproved
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // If not authenticated, proceed
        if (!$request->user()) {
            return $next($request);
        }

        $user = $request->user();

        // System admins always have access
        if ($user->isSystemAdmin()) {
            return $next($request);
        }

        // Citizens don't have system access regardless of approval
        if ($user->role === 'citizen') {
            return $next($request);
        }

        // All other roles (registrar, clerk, admin) must be approved to access system
        if (!$user->isApproved()) {
            return response()->view('errors.forbidden', [], 403);
        }

        return $next($request);
    }
}
