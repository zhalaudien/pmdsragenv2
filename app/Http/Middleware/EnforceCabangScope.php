<?php

namespace App\Http\Middleware;

use App\Models\Cabang;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceCabangScope
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated. Silakan login terlebih dahulu.',
            ], 401);
        }

        $isSuperadmin = $user->isSuperadmin() || $user->role_id === 1;

        if ($isSuperadmin) {
            // Superadmin dapat memilih cabang via header X-Cabang-Id atau query cabang_id
            $headerCabangId = $request->header('X-Cabang-Id') ?: $request->input('cabang_id');
            if ($headerCabangId && Cabang::where('id', $headerCabangId)->exists()) {
                $cabangId = (int)$headerCabangId;
            } else {
                // Default ke cabang pertama jika tidak dispesifikasikan
                $cabangId = (int)(Cabang::first()->id ?? 1);
            }

            $request->attributes->set('effective_cabang_id', $cabangId);
            $request->attributes->set('is_superadmin', true);
            return $next($request);
        }

        // Untuk role non-superadmin (misal: admin_cabang), wajib terikat pada cabang_id
        if (empty($user->cabang_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Akun Anda belum ditautkan dengan cabang manapun.',
                'errors'  => [
                    'cabang' => ['User cabang_id is missing.'],
                ],
            ], 403);
        }

        $request->attributes->set('effective_cabang_id', (int)$user->cabang_id);
        $request->attributes->set('is_superadmin', false);

        return $next($request);
    }
}
