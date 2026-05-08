<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // INI YANG HILANG!
use Symfony\Component\HttpFoundation\Response;

class RoleManager
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $role): Response
    {
        // Mengecek apakah user sudah login DAN apakah rolenya sesuai
        if (Auth::check() && Auth::user()->role == $role) {
            return $next($request);
        }

        // Jika tidak sesuai, lempar ke dashboard utama
        return redirect('/dashboard')->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
    }
}