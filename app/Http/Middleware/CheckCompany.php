<?php

namespace App\Http\Middleware;

use Closure;

class CheckCompany
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $request->company_name = str_replace('-', ' ', $request->company_name);
        if(auth()->check() && auth()->user()->company){

            if (auth()->user()->company->Name != null && strtolower(auth()->user()->company->Name) == $request->company_name) {
                return $next($request);
            } else {
                return redirect()->route('package.course.index');
            }
        }
        
        return redirect('/login');
    }
}
