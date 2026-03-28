<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check() || !auth()->user()->esAdmin()) {
            return redirect()->route('login')->with('error', 'Acceso denegado. Solo administradores.');
        }

        return $next($request);
    }
}
