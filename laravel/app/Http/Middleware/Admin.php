<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class Admin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->check()) {
            if (Auth::guard($guard)->user()->privilege_level >= config('app.privilege_level_admin')) {
                return $next($request);
            }
        }
        if ($request->ajax() || $request->wantsJson())
            return response('Unauthorized.', 401);

        return redirect('/')->with('error', 'Unauthorized.');
    }
}
