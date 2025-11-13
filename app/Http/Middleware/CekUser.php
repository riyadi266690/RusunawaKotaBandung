<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CekUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        $fitur = $request->route()->getName(); // Otomatis ambil dari route name
        $akses = DB::table('role')
            ->where('user_id', $user->id)
            ->where('fitur', $fitur)
            ->where('akses', 1)
            ->exists();

        if (!$akses) {
            return abort(403, 'Akses ditolak untuk fitur: ' . $fitur);
        }

        return $next($request);
    }
}
