<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\ApiSetting;
use App\Models\KegiatanPresensi;
use App\Models\Pemuda;
use App\Models\PresensiDetail;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PresensiController extends BaseApiController
{
    /**
     * POST /api/v1/kegiatan/{id}/presensi/single
     * Simpan / perbarui status presensi 1 orang pemuda realtime
     */
    public function single(Request $request, $id): JsonResponse
    {
        $cabangId = $this->getEffectiveCabangId();

        $kegiatan = KegiatanPresensi::where('id', $id)
            ->where('cabang_id', $cabangId)
            ->first();

        if (!$kegiatan) {
            return $this->errorResponse('Sesi kegiatan presensi tidak ditemukan atau di luar cabang Anda.', 404);
        }

        // Jika kegiatan sudah selesai / terkunci
        if ($kegiatan->status === 'selesai' && !$request->user()->isSuperadmin()) {
            return $this->errorResponse('Sesi kegiatan presensi ini telah selesai dan dikunci. Tidak dapat mengubah data.', 403);
        }

        $validator = Validator::make($request->all(), [
            'pemuda_id'        => 'required|integer',
            'status_kehadiran' => 'required|in:hadir,izin,sakit,alpa',
            'keterangan'       => 'nullable|string|max:500',
            'waktu_presensi'   => 'nullable|string',
            'device_info'      => 'nullable|string|max:100',
        ], [
            'pemuda_id.required'        => 'ID Pemuda wajib disertakan.',
            'status_kehadiran.required' => 'Status kehadiran wajib dipilih.',
            'status_kehadiran.in'       => 'Status kehadiran hanya boleh: hadir, izin, sakit, alpa.',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validasi gagal.', 422, $validator->errors()->toArray());
        }

        $pemudaId = (int)$request->input('pemuda_id');

        // Pastikan pemuda adalah anggota cabang ini
        $pemuda = Pemuda::where('id', $pemudaId)
            ->where('cabang_id', $cabangId)
            ->first();

        if (!$pemuda) {
            return $this->errorResponse('Data pemuda tidak ditemukan pada cabang ini.', 404);
        }

        $waktuPresensi = $request->filled('waktu_presensi')
            ? Carbon::parse($request->input('waktu_presensi'))
            : now();

        $presensi = PresensiDetail::updateOrCreate(
            [
                'kegiatan_presensi_id' => $kegiatan->id,
                'pemuda_id'            => $pemuda->id,
            ],
            [
                'status_kehadiran' => $request->input('status_kehadiran'),
                'keterangan'       => $request->input('keterangan'),
                'waktu_presensi'   => $waktuPresensi,
                'device_info'      => $request->input('device_info', $request->header('User-Agent')),
                'created_by'       => $request->user()->id,
            ]
        );

        $summary = $kegiatan->getRekapSummary();

        $data = [
            'presensi' => [
                'id'                  => $presensi->id,
                'kegiatan_presensi_id'=> $presensi->kegiatan_presensi_id,
                'pemuda_id'           => $presensi->pemuda_id,
                'pemuda_nama'         => $pemuda->name,
                'status_kehadiran'    => $presensi->status_kehadiran,
                'keterangan'          => $presensi->keterangan,
                'waktu_presensi'      => $presensi->waktu_presensi?->toIso8601String(),
                'device_info'         => $presensi->device_info,
            ],
            'summary' => $summary,
        ];

        return $this->successResponse($data, "Presensi {$pemuda->name} berhasil dicatat ({$presensi->status_kehadiran}).");
    }

    /**
     * POST /api/v1/kegiatan/{id}/presensi/bulk
     * Sinkronisasi massal presensi offline (Bulk / Batch Sync)
     */
    public function bulk(Request $request, $id): JsonResponse
    {
        $cabangId = $this->getEffectiveCabangId();

        // Cek izin sinkronisasi offline di pengaturan
        $allowOfflineSync = (int)ApiSetting::get('api_allow_offline_sync', '1') === 1;
        if (!$allowOfflineSync) {
            return $this->errorResponse('Sinkronisasi offline saat ini sedang dinonaktifkan oleh Administrator.', 403);
        }

        $kegiatan = KegiatanPresensi::where('id', $id)
            ->where('cabang_id', $cabangId)
            ->first();

        if (!$kegiatan) {
            return $this->errorResponse('Sesi kegiatan presensi tidak ditemukan atau di luar cabang Anda.', 404);
        }

        // Cek jika status selesai
        if ($kegiatan->status === 'selesai' && !$request->user()->isSuperadmin()) {
            return $this->errorResponse('Sesi kegiatan presensi ini telah selesai dan dikunci.', 403);
        }

        $maxBulkSync = (int)ApiSetting::get('api_max_bulk_sync', '300');

        $validator = Validator::make($request->all(), [
            'device_info'                     => 'nullable|string|max:100',
            'presensi_list'                   => 'required|array|min:1',
            'presensi_list.*.pemuda_id'       => 'required|integer',
            'presensi_list.*.status_kehadiran'=> 'required|in:hadir,izin,sakit,alpa',
            'presensi_list.*.keterangan'      => 'nullable|string|max:500',
            'presensi_list.*.waktu_presensi'  => 'nullable|string',
        ], [
            'presensi_list.required' => 'Daftar presensi (presensi_list) wajib disertakan.',
            'presensi_list.min'      => 'Daftar presensi minimal berisi 1 item.',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validasi data sync gagal.', 422, $validator->errors()->toArray());
        }

        $items = $request->input('presensi_list', []);
        $itemCount = count($items);

        if ($itemCount > $maxBulkSync) {
            return $this->errorResponse("Jumlah data yang disinkronkan ({$itemCount}) melebihi batas maksimum ({$maxBulkSync}) per pengiriman.", 422);
        }

        // Validasi pemuda IDs yang valid di cabang ini
        $requestedPemudaIds = array_column($items, 'pemuda_id');
        $validPemudaIds = Pemuda::where('cabang_id', $cabangId)
            ->whereIn('id', $requestedPemudaIds)
            ->pluck('id')
            ->flip()
            ->toArray();

        $defaultDeviceInfo = $request->input('device_info') ?: ($request->header('User-Agent') ?: 'Presensi PMD Mobile');
        $userId = $request->user()->id;

        $syncedCount = 0;
        $ignoredCount = 0;

        DB::beginTransaction();
        try {
            foreach ($items as $item) {
                $pemudaId = (int)$item['pemuda_id'];

                if (!isset($validPemudaIds[$pemudaId])) {
                    $ignoredCount++;
                    continue;
                }

                $waktuPresensi = !empty($item['waktu_presensi'])
                    ? Carbon::parse($item['waktu_presensi'])
                    : now();

                PresensiDetail::updateOrCreate(
                    [
                        'kegiatan_presensi_id' => $kegiatan->id,
                        'pemuda_id'            => $pemudaId,
                    ],
                    [
                        'status_kehadiran' => $item['status_kehadiran'],
                        'keterangan'       => $item['keterangan'] ?? null,
                        'waktu_presensi'   => $waktuPresensi,
                        'device_info'      => $defaultDeviceInfo,
                        'created_by'       => $userId,
                    ]
                );

                $syncedCount++;
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->errorResponse('Gagal menyinkronkan data presensi: ' . $e->getMessage(), 500);
        }

        $summary = $kegiatan->getRekapSummary();

        $data = [
            'kegiatan_id'   => $kegiatan->id,
            'total_request' => count($items),
            'synced_count'  => $syncedCount,
            'ignored_count' => $ignoredCount,
            'summary'       => $summary,
        ];

        return $this->successResponse($data, "Sinkronisasi berhasil: {$syncedCount} data tersimpan, {$ignoredCount} diabaikan.");
    }
}
