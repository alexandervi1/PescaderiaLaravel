<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CabecerasSeguridad
{
    /**
     * Cabeceras independientes del proxy. La CSP se deja fuera a propósito:
     * el panel usa Bootstrap, DataTables, Chart.js y FontAwesome por CDN.
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        return $response;
    }
}
