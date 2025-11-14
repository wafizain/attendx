<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckFirstLogin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (auth()->check()) {
            $user = auth()->user();
            
            // Check if user must change password
            if ($user->must_change_password) {
                // Allow access to change password route
                if (!$request->routeIs('profile.change-password') && !$request->routeIs('profile.update-password')) {
                    return redirect()->route('profile.change-password')
                        ->with('warning', 'Anda harus mengganti password sementara sebelum melanjutkan.');
                }
            }
        }

        return $next($request);
    }
}
