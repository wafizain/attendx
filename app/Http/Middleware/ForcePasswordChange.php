<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class ForcePasswordChange
{
    public function handle($request, Closure $next)
    {
        $user = Auth::user();
        if ($user && $user->role === 'dosen' && is_null($user->password_changed_at)) {
            if (!$request->is('password/first-change') && !$request->is('logout')) {
                return redirect('/password/first-change');
            }
        }
        return $next($request);
    }
}
