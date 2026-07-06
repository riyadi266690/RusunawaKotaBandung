<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        // Fallback jika server web tidak mem-passing PHP_AUTH_USER & PHP_AUTH_PW
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

        // Jika username atau password kosong, langsung tolak
        if (empty($username) || empty($password)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        // Lakukan autentikasi menggunakan data dari tabel `users` (hanya untuk satu request)
        if (!Auth::once(['email' => $username, 'password' => $password])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        return $next($request);
    }
}
