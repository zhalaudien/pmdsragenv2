<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\KegiatanPresensi;
use App\Models\Pemuda;
use App\Models\PresensiDetail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class KegiatanController extends BaseApiController
{
    /**
     * GET /api/v1/kegiatan
     * Daftar sesi kegiatan presensi cabang
     */
    public function index(Request $request): JsonResponse
    {
        $cabangId = $this->getEffectiveCabangId();

        $query = KegiatanPresensi::with(['creator:id,name'])
            ->where('cabang_id', $cabangId);

        // Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filter tanggal / rentang
        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal', $request->input('tanggal'));
        }

        if ($request->filled('bulan') && $request->filled('tahun')) {
            $query->whereMonth('tanggal', $request->input('bulan'))
                  ->whereYear('tanggal', $request->input('tahun'));
        }

        // Search
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('nama_kegiatan', 'like', "%{$search}%")
                  ->orWhere('lokasi', 'like', "%{$search}%")
                  ->orWhere('pemateri', 'like', "%{$search}%");
            });
        }

        $list = $query->orderBy('tanggal', 'DESC')
            ->orderBy('id', 'DESC')
            ->get()
            ->map(function ($item) {
                $summary = $item->getRekapSummary();

                return [
                    'id'               => $item->id,
                    'cabang_id'        => $item->cabang_id,
                    'nama_kegiatan'    => $item->nama_kegiatan,
                    'tanggal'          => $item->tanggal->format('Y-m-d'),
                    'jam_mulai'        => $item->jam_mulai ? substr($item->jam_mulai, 0, 5) : null,
                    'jam_selesai'      => $item->jam_selesai ? substr($item->jam_selesai, 0, 5) : null,
                    'lokasi'           => $item->lokasi,
                    'pemateri'         => $item->pemateri,
                    'target_peserta'   => $item->target_peserta,
                    'status'           => $item->status,
                    'catatan'          => $item->catatan,
                    'creator'          => $item->creator ? ['id' => $item->creator->id, 'name' => $item->creator->name] : null,
                    'created_at'       => $item->created_at?->toIso8601String(),
                    'summary'          => [
                        'total_pemuda'     => $summary['total_pemuda'],
                        'hadir'            => $summary['hadir'],
                        'izin'             => $summary['izin'],
                        'sakit'            => $summary['sakit'],
                        'alpa'             => $summary['alpa'],
                        'persentase_hadir' => $summary['persentase_hadir'],
                    ],
                ];
            });

        return $this->successResponse($list, 'Daftar sesi kegiatan presensi berhasil diambil.');
    }

    /**
     * POST /api/v1/kegiatan
     * Buat sesi kegiatan presensi baru
     */
    public function store(Request $request): JsonResponse
    {
        $cabangId = $this->getEffectiveCabangId();

        $validator = Validator::make($request->all(), [
            'nama_kegiatan'  => 'required|string|max:150',
            'tanggal'        => 'required|date',
            'jam_mulai'      => 'nullable|string|max:10',
            'jam_selesai'    => 'nullable|string|max:10',
            'lokasi'         => 'nullable|string|max:200',
            'pemateri'       => 'nullable|string|max:150',
            'target_peserta' => 'nullable|in:semua,pemuda,pemudi',
            'status'         => 'nullable|in:draft,berlangsung,selesai',
            'catatan'        => 'nullable|string',
        ], [
            'nama_kegiatan.required' => 'Nama kegiatan presensi wajib diisi.',
            'tanggal.required'       => 'Tanggal kegiatan wajib diisi.',
            'tanggal.date'           => 'Format tanggal tidak valid.',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validasi gagal.', 422, $validator->errors()->toArray());
        }

        $kegiatan = KegiatanPresensi::create([
            'cabang_id'      => $cabangId,
            'nama_kegiatan'  => trim($request->input('nama_kegiatan')),
            'tanggal'        => $request->input('tanggal'),
            'jam_mulai'      => $request->input('jam_mulai'),
            'jam_selesai'    => $request->input('jam_selesai'),
            'lokasi'         => $request->input('lokasi'),
            'pemateri'       => $request->input('pemateri'),
            'target_peserta' => $request->input('target_peserta', 'semua'),
            'status'         => $request->input('status', 'berlangsung'),
            'catatan'        => $request->input('catatan'),
            'created_by'     => $request->user()->id,
        ]);

        $kegiatan->load('creator:id,name');

        $responseData = [
            'id'             => $kegiatan->id,
            'cabang_id'      => $kegiatan->cabang_id,
            'nama_kegiatan'  => $kegiatan->nama_kegiatan,
            'tanggal'        => $kegiatan->tanggal->format('Y-m-d'),
            'jam_mulai'      => $kegiatan->jam_mulai ? substr($kegiatan->jam_mulai, 0, 5) : null,
            'jam_selesai'    => $kegiatan->jam_selesai ? substr($kegiatan->jam_selesai, 0, 5) : null,
            'lokasi'         => $kegiatan->lokasi,
            'pemateri'       => $kegiatan->pemateri,
            'target_peserta' => $kegiatan->target_peserta,
            'status'         => $kegiatan->status,
            'catatan'        => $kegiatan->catatan,
            'creator'        => $kegiatan->creator ? ['id' => $kegiatan->creator->id, 'name' => $kegiatan->creator->name] : null,
            'created_at'     => $kegiatan->created_at?->toIso8601String(),
        ];

        return $this->successResponse($responseData, 'Sesi kegiatan presensi berhasil dibuat.', 201);
    }

    /**
     * GET /api/v1/kegiatan/{id}
     * Detail sesi kegiatan presensi beserta status kehadiran seluruh pemuda
     */
    public function show(Request $request, $id): JsonResponse
    {
        $cabangId = $this->getEffectiveCabangId();

        $kegiatan = KegiatanPresensi::with(['creator:id,name', 'cabang:id,name,code'])
            ->where('id', $id)
            ->where('cabang_id', $cabangId)
            ->first();

        if (!$kegiatan) {
            return $this->errorResponse('Sesi kegiatan presensi tidak ditemukan atau di luar wewenang cabang Anda.', 404);
        }

        // Ambil daftar pemuda di cabang ini
        $queryPemuda = Pemuda::where('cabang_id', $cabangId)
            ->where('status_data', 'active');

        if ($kegiatan->target_peserta === 'pemuda') {
            $queryPemuda->where('gender', 'L');
        } elseif ($kegiatan->target_peserta === 'pemudi') {
            $queryPemuda->where('gender', 'P');
        }

        $allPemuda = $queryPemuda->orderBy('name', 'ASC')->get();

        // Ambil presensi_detail untuk kegiatan ini
        $presensiMap = PresensiDetail::where('kegiatan_presensi_id', $kegiatan->id)
            ->get()
            ->keyBy('pemuda_id');

        $checklist = $allPemuda->map(function ($p) use ($presensiMap) {
            $presensi = $presensiMap->get($p->id);

            $fotoUrl = null;
            if (!empty($p->foto)) {
                $fotoUrl = asset('uploads/pemuda/' . $p->foto);
            }

            return [
                'pemuda_id'           => $p->id,
                'registration_number' => $p->registration_number,
                'name'                => $p->name,
                'gender'              => $p->gender,
                'phone'               => $p->phone,
                'foto_url'            => $fotoUrl,
                'status_verifikasi'   => $p->status_verifikasi,
                'is_presensi'         => $presensi !== null,
                'presensi_id'         => $presensi?->id,
                'status_kehadiran'    => $presensi ? $presensi->status_kehadiran : 'alpa',
                'keterangan'          => $presensi?->keterangan,
                'waktu_presensi'      => $presensi?->waktu_presensi?->toIso8601String(),
                'device_info'         => $presensi?->device_info,
            ];
        });

        $summary = $kegiatan->getRekapSummary();

        $data = [
            'kegiatan' => [
                'id'             => $kegiatan->id,
                'cabang_id'      => $kegiatan->cabang_id,
                'nama_kegiatan'  => $kegiatan->nama_kegiatan,
                'tanggal'        => $kegiatan->tanggal->format('Y-m-d'),
                'jam_mulai'      => $kegiatan->jam_mulai ? substr($kegiatan->jam_mulai, 0, 5) : null,
                'jam_selesai'    => $kegiatan->jam_selesai ? substr($kegiatan->jam_selesai, 0, 5) : null,
                'lokasi'         => $kegiatan->lokasi,
                'pemateri'       => $kegiatan->pemateri,
                'target_peserta' => $kegiatan->target_peserta,
                'status'         => $kegiatan->status,
                'catatan'        => $kegiatan->catatan,
                'creator'        => $kegiatan->creator ? ['id' => $kegiatan->creator->id, 'name' => $kegiatan->creator->name] : null,
                'created_at'     => $kegiatan->created_at?->toIso8601String(),
            ],
            'summary'   => $summary,
            'checklist' => $checklist,
        ];

        return $this->successResponse($data, 'Detail kegiatan presensi berhasil diambil.');
    }

    /**
     * PUT /api/v1/kegiatan/{id}/status
     * Tutup / kunci sesi kegiatan (misal: selesai)
     */
    public function updateStatus(Request $request, $id): JsonResponse
    {
        $cabangId = $this->getEffectiveCabangId();

        $kegiatan = KegiatanPresensi::where('id', $id)
            ->where('cabang_id', $cabangId)
            ->first();

        if (!$kegiatan) {
            return $this->errorResponse('Sesi kegiatan presensi tidak ditemukan atau di luar wewenang cabang Anda.', 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:draft,berlangsung,selesai',
        ], [
            'status.required' => 'Status kegiatan wajib dipilih.',
            'status.in'       => 'Pilihan status hanya: draft, berlangsung, selesai.',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validasi gagal.', 422, $validator->errors()->toArray());
        }

        $newStatus = $request->input('status');
        $kegiatan->update(['status' => $newStatus]);

        $summary = $kegiatan->getRekapSummary();

        return $this->successResponse([
            'id'      => $kegiatan->id,
            'status'  => $kegiatan->status,
            'summary' => $summary,
        ], "Status kegiatan presensi berhasil diperbarui menjadi '{$newStatus}'.");
    }

    /**
     * GET /api/v1/kegiatan/{id}/rekap
     * Ringkasan statistik kehadiran & teks siap kirim WhatsApp
     */
    public function rekap(Request $request, $id): JsonResponse
    {
        $cabangId = $this->getEffectiveCabangId();

        $kegiatan = KegiatanPresensi::with(['creator:id,name', 'cabang:id,name,code'])
            ->where('id', $id)
            ->where('cabang_id', $cabangId)
            ->first();

        if (!$kegiatan) {
            return $this->errorResponse('Sesi kegiatan presensi tidak ditemukan atau di luar wewenang cabang Anda.', 404);
        }

        $summary = $kegiatan->getRekapSummary();
        $waText  = $kegiatan->generateWhatsAppText();

        $data = [
            'kegiatan_id'   => $kegiatan->id,
            'nama_kegiatan' => $kegiatan->nama_kegiatan,
            'tanggal'       => $kegiatan->tanggal->format('Y-m-d'),
            'status'        => $kegiatan->status,
            'summary'       => $summary,
            'whatsapp_text' => $waText,
        ];

        return $this->successResponse($data, 'Data rekapitulasi kehadiran berhasil diambil.');
    }
}
