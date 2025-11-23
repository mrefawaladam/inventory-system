<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (!auth()->check()) {
            abort(401, 'Unauthenticated');
        }

        if (!auth()->user()->can($permission)) {
            \App\Services\SecurityLogService::logPermissionDenied($permission, request()->route()->getName());
            abort(403, 'Unauthorized. You do not have permission to perform this action.');
        }

        return $next($request);
    }
}

