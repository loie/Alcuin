<?php

namespace App\Http\Middleware;

use Closure;

class OldAge
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
        if ($request->input('email') === 'lorenz.merdian@googlemail.com') {
            die('LOL');
        }
        return $next($request);
    }
}
