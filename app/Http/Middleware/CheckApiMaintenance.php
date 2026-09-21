<?php

namespace App\Http\Middleware;

use App\Models\ApiSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckApiMaintenance
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Izinkan endpoint konfigurasi/info publik
        if ($request->is('api/v1/config') || $request->is('api/v1/status')) {
            return $next($request);
        }

        $isEnabled = ApiSetting::get('api_presensi_enabled', '1');

        if ($isEnabled === '0' || $isEnabled === false || $isEnabled === 0) {
            // Superadmin tetap dapat mengakses jika sudah terautentikasi
            $user = $request->user();
            if ($user && ($user->isSuperadmin() || $user->role_id === 1)) {
                return $next($request);
            }

            $message = ApiSetting::get(
                'api_maintenance_message',
                'Layanan API Presensi PMD sedang dalam pemeliharaan sistem berkala. Silakan coba kembali beberapa saat lagi.'
            );

            return response()->json([
                'success' => false,
                'message' => $message,
                'errors'  => [
                    'maintenance' => true,
                ],
                'meta' => [
                    'timestamp' => now()->toIso8601String(),
                ],
            ], 503);
        }

        return $next($request);
    }
}
