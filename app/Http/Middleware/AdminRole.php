<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $userRole = auth()->user()?->role?->name ?? session()->get('role');

        if (!$userRole) {
            return redirect()->route('login')->with('error', 'Sesi Anda telah berakhir.');
        }

        if (empty($roles)) {
            return $next($request);
        }

        if (in_array($userRole, $roles, true)) {
            return $next($request);
        }

        abort(403, 'Akses Ditolak: Anda tidak memiliki izin untuk mengakses halaman ini.');
    }
}
