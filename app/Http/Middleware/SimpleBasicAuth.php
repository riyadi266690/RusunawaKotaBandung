<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SimpleBasicAuth
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->getUser() !== 'tes' || $request->getPassword() !== 'pasword') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401, [
                'WWW-Authenticate' => 'Basic realm="API Access"'
            ]);
        }

        return $next($request);
    }
}
