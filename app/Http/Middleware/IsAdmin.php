<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Periksa apakah pengguna sudah login
        if (!Auth::check()) {
            return redirect()->route('login')->withErrors([
                'message' => 'Anda harus login untuk mengakses halaman ini.',
            ]);
        }

        // Periksa peran pengguna
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('profile.show')->with('error', 'You are not authorized to access this page. Because your role not admin');
        }

        return $next($request);
    }
}
