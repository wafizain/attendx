<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class DebugRequest
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Log semua request ke route dosen
        if ($request->is('dosen/*')) {
            Log::info('DebugRequest: Dosen route accessed', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'user_authenticated' => auth()->check(),
                'user_id' => auth()->check() ? auth()->id() : null,
                'user_role' => auth()->check() ? auth()->user()->role : null,
                'session_id' => session()->getId(),
                'headers' => $request->headers->all(),
                'middleware_stack' => $request->route()?->middleware(),
            ]);
        }

        return $next($request);
    }
}
