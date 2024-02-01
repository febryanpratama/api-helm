<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class Roles
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, ...$levels)
    {
        if (in_array($request->user()->role_id, $levels)) {
            // Check Suspend Account
            if ($request->user()->is_take_down == 1) {
               Auth::logout();

               return redirect('/');
            }

            return $next($request);
        }

        return redirect('/dashboard');
    }
}
