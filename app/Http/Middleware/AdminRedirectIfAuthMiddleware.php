<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminRedirectIfAuthMiddleware
{
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (auth($guard)->check()) {
                return redirect()->route('admin.dashboard');
            }
        }

        return $next($request);
    }
}
