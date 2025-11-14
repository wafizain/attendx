<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Log debug information
        Log::info('CheckRole Middleware - Debug Info', [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'user_authenticated' => auth()->check(),
            'user_id' => auth()->check() ? auth()->id() : null,
            'user_role' => auth()->check() ? auth()->user()->role : null,
            'required_roles' => $roles,
            'session_data' => session()->all(),
        ]);

        if (!auth()->check()) {
            Log::warning('CheckRole: User not authenticated', ['url' => $request->fullUrl()]);
            return redirect()->route('login');
        }

        $userRole = auth()->user()->role;
        
        Log::info('CheckRole: Checking role', [
            'user_role' => $userRole,
            'required_roles' => $roles,
            'role_match' => in_array($userRole, $roles)
        ]);

        if (!in_array($userRole, $roles)) {
            Log::error('CheckRole: Access denied', [
                'user_id' => auth()->id(),
                'user_role' => $userRole,
                'required_roles' => $roles,
                'url' => $request->fullUrl()
            ]);
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        Log::info('CheckRole: Access granted', [
            'user_id' => auth()->id(),
            'user_role' => $userRole,
            'url' => $request->fullUrl()
        ]);

        return $next($request);
    }
}
