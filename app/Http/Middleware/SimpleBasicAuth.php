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
        $username = $request->getUser();
        $password = $request->getPassword();

        // Fallback jika server web (seperti Nginx/Apache + PHP-FPM) tidak mem-passing PHP_AUTH_USER & PHP_AUTH_PW
        if (null === $username || null === $password) {
            $authHeader = $request->header('Authorization');
            if ($authHeader && preg_match('/Basic\s+(.*)$/i', $authHeader, $matches)) {
                $decoded = explode(':', base64_decode($matches[1]), 2);
                if (count($decoded) === 2) {
                    $username = $decoded[0];
                    $password = $decoded[1];
                }
            }
        }

        // Cek username 'tes' dan password 'pasword' atau 'password' (toleransi typo)
        if ($username !== 'tes' || ($password !== 'pasword' && $password !== 'password')) {
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
