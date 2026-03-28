<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ClienteMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check() || auth()->user()->esVisitante()) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión para acceder.');
        }

        return $next($request);
    }
}
