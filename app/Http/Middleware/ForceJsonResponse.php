<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceJsonResponse
{
    /**
     * Handle an incoming request.
     * 
     * Forces where dont have accept setted.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->hasHeader('Accept')) {
            $request->headers->set('Accept', 'application/json');
        }

        return $next($request);
    }
}
