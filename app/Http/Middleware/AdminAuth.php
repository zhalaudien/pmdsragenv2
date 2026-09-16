<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() && !session()->has('user_id')) {
            session()->put('redirect_url', $request->fullUrl());
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu untuk mengakses halaman ini.');
        }

        $user = auth()->user();
        if ($user && $user->status !== 1) {
            auth()->logout();
            session()->flush();
            return redirect()->route('login')->with('error', 'Akun Anda dinonaktifkan. Silakan hubungi Administrator.');
        }

        return $next($request);
    }
}
