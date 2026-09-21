<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Cabang;
use App\Models\Pemuda;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CabangPemudaController extends BaseApiController
{
    /**
     * GET /api/v1/cabang/pemuda
     * Ambil seluruh pemuda di cabang yang sedang aktif
     */
    public function index(Request $request): JsonResponse
    {
        $cabangId = $this->getEffectiveCabangId();
        $cabang   = Cabang::find($cabangId);

        if (!$cabang) {
            return $this->errorResponse('Data cabang tidak ditemukan.', 404);
        }

        $query = Pemuda::where('cabang_id', $cabangId)
            ->where('status_data', 'active');

        // Filter status verifikasi jika diminta (misal: verified_only=1)
        if ($request->boolean('verified_only')) {
            $query->where('status_verifikasi', 'verified');
        } elseif ($request->filled('status_verifikasi')) {
            $query->where('status_verifikasi', $request->input('status_verifikasi'));
        }

        // Filter Gender (L/P)
        if ($request->filled('gender')) {
            $gender = strtoupper(trim($request->input('gender')));
            if (in_array($gender, ['L', 'P'])) {
                $query->where('gender', $gender);
            }
        }

        // Filter Search
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('registration_number', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $pemudaList = $query->select([
            'id',
            'cabang_id',
            'registration_number',
            'name',
            'gender',
            'birth_place',
            'birth_date',
            'phone',
            'foto',
            'status_verifikasi',
            'status_data',
            'updated_at',
        ])
        ->orderBy('name', 'ASC')
        ->get()
        ->map(function ($p) {
            $fotoUrl = null;
            if (!empty($p->foto)) {
                $fotoUrl = asset('uploads/pemuda/' . $p->foto);
            }

            return [
                'id'                  => $p->id,
                'cabang_id'           => $p->cabang_id,
                'registration_number' => $p->registration_number,
                'name'                => $p->name,
                'gender'              => $p->gender,
                'gender_label'        => $p->gender === 'L' ? 'Pemuda (L)' : 'Pemudi (P)',
                'birth_place'         => $p->birth_place,
                'birth_date'          => $p->birth_date ? $p->birth_date->format('Y-m-d') : null,
                'phone'               => $p->phone,
                'foto_url'            => $fotoUrl,
                'status_verifikasi'   => $p->status_verifikasi,
                'status_data'         => $p->status_data,
                'updated_at'          => $p->updated_at?->toIso8601String(),
            ];
        });

        // Ringkasan gender
        $totalAll = $pemudaList->count();
        $totalL   = $pemudaList->where('gender', 'L')->count();
        $totalP   = $pemudaList->where('gender', 'P')->count();

        $extraMeta = [
            'cabang' => [
                'id'   => $cabang->id,
                'code' => $cabang->code,
                'name' => $cabang->name,
            ],
            'counts' => [
                'total'  => $totalAll,
                'pemuda' => $totalL,
                'pemudi' => $totalP,
            ],
        ];

        return $this->successResponse($pemudaList, 'Daftar pemuda cabang berhasil diambil.', 200, $extraMeta);
    }
}
