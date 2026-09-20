<?php

namespace App\Services;

use App\Models\Pemuda;
use App\Models\Wilayah;
use App\Models\Cabang;
use App\Models\Alamat;
use App\Models\Pendidikan;
use App\Models\Pekerjaan;
use App\Models\Organisasi;
use App\Models\Province;
use App\Models\Regency;
use App\Models\District;
use App\Models\Village;
use App\Models\EducationLevel;
use App\Models\JobStatus;
use App\Models\MtaSyncLog;
use App\Models\MtaSyncQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MtaSyncService
{
    protected MtaApiService $apiService;

    public function __construct(?MtaApiService $apiService = null)
    {
        $this->apiService = $apiService ?? new MtaApiService();
    }

    public function syncCabang(?string $perwakilanUuid = null, ?int $wilayahId = null, bool $autoCreate = true): array
    {
        $perwakilanUuid = $perwakilanUuid ?: $this->apiService->getSragenUuid();
        $response = $this->apiService->getCabangSragenList();

        if (!($response['success'] ?? false) || !isset($response['data']) || !is_array($response['data'])) {
            $msg = $response['message'] ?? 'Gagal mengambil data cabang Perwakilan Sragen dari server API MTA.';
            MtaSyncLog::log('cabang', 'failed', 0, $msg);
            return [
                'success' => false,
                'message' => $msg,
            ];
        }

        $mtaCabangList = $response['data'];
        $syncedCount   = 0;
        $createdCount  = 0;
        $updatedCount  = 0;
        $unmatched     = [];

        if (!$wilayahId) {
            $firstWilayah = Wilayah::first();
            $wilayahId = $firstWilayah ? (int) $firstWilayah->id : 1;
        }

        foreach ($mtaCabangList as $item) {
            $uuid   = $item['uuid'] ?? '';
            $nama   = trim($item['nama'] ?? '');
            $kode   = trim($item['kode'] ?? '');
            $alamat = trim($item['alamat'] ?? '');

            if (empty($uuid) || empty($nama)) {
                continue;
            }

            $existing = Cabang::where('mta_uuid', $uuid)->first();

            if (!$existing) {
                $existing = Cabang::whereRaw('LOWER(TRIM(name)) = ?', [strtolower($nama)])->first();
            }

            if ($existing) {
                $updateData = [
                    'mta_uuid'           => $uuid,
                    'mta_last_synced_at' => now(),
                ];
                if (!empty($kode)) {
                    $updateData['code'] = $kode;
                }
                if (empty($existing->alamat) && !empty($alamat)) {
                    $updateData['alamat'] = $alamat;
                }

                $existing->update($updateData);
                $updatedCount++;
                $syncedCount++;
            } elseif ($autoCreate) {
                Cabang::create([
                    'wilayah_id'         => $wilayahId,
                    'code'               => !empty($kode) ? $kode : null,
                    'name'               => $nama,
                    'alamat'             => !empty($alamat) ? $alamat : null,
                    'mta_uuid'           => $uuid,
                    'mta_last_synced_at' => now(),
                ]);
                $createdCount++;
                $syncedCount++;
            } else {
                $unmatched[] = $nama;
            }
        }

        $logMsg = "Sinkronisasi Cabang MTA selesai. Total diproses: {$syncedCount} (Dibuat baru: {$createdCount}, Diperbarui: {$updatedCount}).";
        MtaSyncLog::log('cabang', 'success', $syncedCount, $logMsg);

        return [
            'success'   => true,
            'message'   => $logMsg,
            'total'     => count($mtaCabangList),
            'synced'    => $syncedCount,
            'created'   => $createdCount,
            'updated'   => $updatedCount,
            'unmatched' => $unmatched,
        ];
    }

    public function syncWargaToPemuda(array $wargaData, ?int $cabangId = null, ?int $userId = null): array
    {
        $wargaUuid = $wargaData['uuid'] ?? '';
        $nama      = trim($wargaData['nama'] ?? '');

        if (empty($nama)) {
            return ['success' => false, 'message' => 'Nama warga MTA tidak valid.'];
        }

        // Pastikan detail lengkap warga ditarik jika belum ada
        if ((empty($wargaData['desa']) || empty($wargaData['alamat_rtrw'])) && !empty($wargaUuid)) {
            $detailRes = $this->apiService->getWargaDetail($wargaUuid);
            if (($detailRes['success'] ?? false) && !empty($detailRes['data'])) {
                $wargaData = array_merge($wargaData, $detailRes['data']);
            }
        }

        // Resolusi cabang otomatis sesuai basis data cabang MTA Pusat
        if (!$cabangId || $cabangId <= 0) {
            $cabangUuid = $wargaData['cabang_uuid'] ?? null;
            $cabangName = trim($wargaData['cabang'] ?? ($wargaData['cabang_nama'] ?? ''));

            $cabang = null;
            if (!empty($cabangUuid)) {
                $cabang = Cabang::where('mta_uuid', $cabangUuid)->first();
            }
            if (!$cabang && !empty($cabangName)) {
                $cabang = Cabang::whereRaw('LOWER(TRIM(name)) = ?', [strtolower($cabangName)])->first();
            }

            if (!$cabang) {
                return [
                    'success' => false,
                    'message' => "Cabang MTA '{$cabangName}' tidak ditemukan di database cabang lokal.",
                ];
            }

            $cabangId = (int) $cabang->id;
        }

        $gender     = in_array(strtoupper($wargaData['kelamin'] ?? 'L'), ['L', 'P'], true) ? strtoupper($wargaData['kelamin']) : 'L';
        $birthDate  = !empty($wargaData['lahir']) ? date('Y-m-d', strtotime($wargaData['lahir'])) : null;
        $birthPlace = !empty($wargaData['tempat_lahir']) ? trim($wargaData['tempat_lahir']) : 'Sragen';
        $phone      = !empty($wargaData['nohp']) ? preg_replace('/[^0-9+]/', '', $wargaData['nohp']) : null;
        $bloodType  = $this->matchBloodType($wargaData['goldar'] ?? '');
        $mtaStatus  = $wargaData['status'] ?? 'Warga';
        $marital    = $this->matchMaritalStatus($wargaData['menikah'] ?? '');
        $email      = !empty($wargaData['email']) ? trim($wargaData['email']) : null;
        $ayahUuid   = !empty($wargaData['ayah_uuid']) ? $wargaData['ayah_uuid'] : null;
        $ibuUuid    = !empty($wargaData['ibu_uuid']) ? $wargaData['ibu_uuid'] : null;
        $fotoUrl    = (!empty($wargaData['foto']) && !str_contains($wargaData['foto'], 'default.png')) ? $wargaData['foto'] : null;

        $existingPemuda = null;
        if (!empty($wargaUuid)) {
            $existingPemuda = Pemuda::where('mta_warga_uuid', $wargaUuid)->first();
        }
        if (!$existingPemuda && !empty($birthDate)) {
            $existingPemuda = Pemuda::findExistingPemuda($nama, $gender, $birthDate, $cabangId);
        }

        DB::beginTransaction();
        try {
            if ($existingPemuda) {
                $updateData = [
                    'mta_warga_uuid'    => $wargaUuid ?: $existingPemuda->mta_warga_uuid,
                    'mta_status_warga'  => $mtaStatus,
                    'mta_synced_at'     => now(),
                    'status_verifikasi' => 'verified',
                    'marital_status'    => $marital,
                    'blood_type'        => $bloodType ?: $existingPemuda->blood_type,
                    'phone'             => $phone ?: $existingPemuda->phone,
                    'email'             => $email ?: $existingPemuda->email,
                    'birth_place'       => ($existingPemuda->birth_place === 'Sragen' && $birthPlace !== 'Sragen') ? $birthPlace : ($existingPemuda->birth_place ?: $birthPlace),
                    'birth_date'        => $existingPemuda->birth_date ?: ($birthDate ?: date('Y-m-d', strtotime('-20 years'))),
                    'mta_ayah_uuid'     => $ayahUuid ?: $existingPemuda->mta_ayah_uuid,
                    'mta_ibu_uuid'      => $ibuUuid ?: $existingPemuda->mta_ibu_uuid,
                    'mta_foto_url'      => $fotoUrl ?: $existingPemuda->mta_foto_url,
                ];

                $existingPemuda->update($updateData);
                $pemudaId = $existingPemuda->id;
                $action   = 'updated';

                $this->matchOrCreateAlamat($pemudaId, $wargaData);
                $this->matchOrCreatePendidikan($pemudaId, $wargaData);
                $this->matchOrCreatePekerjaan($pemudaId, $wargaData);
            } else {
                $regNumber = Pemuda::generateRegistrationNumber($cabangId, $birthDate);

                $pemuda = Pemuda::create([
                    'cabang_id'           => $cabangId,
                    'registration_number' => $regNumber,
                    'name'                => $nama,
                    'gender'              => $gender,
                    'marital_status'      => $marital,
                    'blood_type'          => $bloodType,
                    'birth_place'         => $birthPlace,
                    'birth_date'          => $birthDate ?: date('Y-m-d', strtotime('-20 years')),
                    'phone'               => $phone,
                    'email'               => $email,
                    'status_verifikasi'   => 'verified',
                    'status_data'         => 'active',
                    'mta_warga_uuid'      => $wargaUuid,
                    'mta_status_warga'    => $mtaStatus,
                    'mta_ayah_uuid'       => $ayahUuid,
                    'mta_ibu_uuid'        => $ibuUuid,
                    'mta_foto_url'        => $fotoUrl,
                    'mta_synced_at'       => now(),
                    'created_by'          => $userId ?? auth()->id(),
                ]);

                $pemudaId = $pemuda->id;
                $action   = 'created';

                $this->matchOrCreateAlamat($pemudaId, $wargaData);
                $this->matchOrCreatePendidikan($pemudaId, $wargaData);
                $this->matchOrCreatePekerjaan($pemudaId, $wargaData);
            }

            DB::commit();

            return [
                'success'   => true,
                'action'    => $action,
                'pemuda_id' => $pemudaId,
                'name'      => $nama,
                'message'   => "Data pemuda '{$nama}' berhasil disinkronkan ({$action}) dengan seluruh data MTA Pusat.",
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('[MtaSyncService] syncWargaToPemuda Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal menyimpan sinkronisasi warga: ' . $e->getMessage(),
            ];
        }
    }

    public function syncSinglePemuda(int $pemudaId, ?int $userId = null): array
    {
        $pemuda = Pemuda::with(['cabang', 'alamat', 'pendidikan', 'pekerjaan'])->find($pemudaId);
        if (!$pemuda) {
            return ['success' => false, 'message' => 'Data pemuda tidak ditemukan.'];
        }

        $wargaData = null;

        if (!empty($pemuda->mta_warga_uuid)) {
            $res = $this->apiService->getWargaDetail($pemuda->mta_warga_uuid);
            if (($res['success'] ?? false) && !empty($res['data'])) {
                $wargaData = $res['data'];
            }
        }

        if (!$wargaData) {
            $searchRes = $this->apiService->searchWarga($pemuda->name, [
                'kelamin' => $pemuda->gender,
                'limit'   => 5,
            ]);

            if (($searchRes['success'] ?? false) && !empty($searchRes['data'])) {
                $pemudaBirth = !empty($pemuda->birth_date) ? date('Y-m-d', strtotime($pemuda->birth_date)) : null;

                foreach ($searchRes['data'] as $candidate) {
                    $candBirth = !empty($candidate['lahir']) ? date('Y-m-d', strtotime($candidate['lahir'])) : null;
                    if ($pemudaBirth && $candBirth && $pemudaBirth === $candBirth) {
                        $wargaData = $candidate;
                        break;
                    }
                }

                if (!$wargaData && count($searchRes['data']) === 1) {
                    $wargaData = $searchRes['data'][0];
                }
            }
        }

        if (!$wargaData) {
            return [
                'success' => false,
                'matched' => false,
                'message' => "Tidak ditemukan kecocokan data '{$pemuda->name}' di database MTA Pusat.",
            ];
        }

        // Jika data dari pencarian ringkas (belum ada detail desa/alamat_rtrw/ayah/ibu/foto), tarik detail lengkap
        if (!empty($wargaData['uuid']) && !isset($wargaData['desa'])) {
            $detailRes = $this->apiService->getWargaDetail($wargaData['uuid']);
            if (($detailRes['success'] ?? false) && !empty($detailRes['data'])) {
                $wargaData = array_merge($wargaData, $detailRes['data']);
            }
        }

        $wargaUuid  = $wargaData['uuid'] ?? $pemuda->mta_warga_uuid;
        $birthDate  = !empty($wargaData['lahir']) ? date('Y-m-d', strtotime($wargaData['lahir'])) : $pemuda->birth_date;
        $birthPlace = !empty($wargaData['tempat_lahir']) ? trim($wargaData['tempat_lahir']) : ($pemuda->birth_place ?: 'Sragen');
        $phone      = !empty($wargaData['nohp']) ? preg_replace('/[^0-9+]/', '', $wargaData['nohp']) : $pemuda->phone;
        $bloodType  = $this->matchBloodType($wargaData['goldar'] ?? '') ?: $pemuda->blood_type;
        $mtaStatus  = $wargaData['status'] ?? ($pemuda->mta_status_warga ?: 'Warga');
        $marital    = !empty($wargaData['menikah']) ? $this->matchMaritalStatus($wargaData['menikah']) : $pemuda->marital_status;
        $email      = !empty($wargaData['email']) ? trim($wargaData['email']) : $pemuda->email;
        $ayahUuid   = !empty($wargaData['ayah_uuid']) ? $wargaData['ayah_uuid'] : $pemuda->mta_ayah_uuid;
        $ibuUuid    = !empty($wargaData['ibu_uuid']) ? $wargaData['ibu_uuid'] : $pemuda->mta_ibu_uuid;
        $fotoUrl    = (!empty($wargaData['foto']) && !str_contains($wargaData['foto'], 'default.png')) ? $wargaData['foto'] : $pemuda->mta_foto_url;

        $updateData = [
            'mta_warga_uuid'    => $wargaUuid,
            'mta_status_warga'  => $mtaStatus,
            'status_verifikasi' => 'verified',
            'mta_synced_at'     => now(),
            'marital_status'    => $marital,
            'blood_type'        => $bloodType,
            'phone'             => $phone,
            'email'             => $email,
            'birth_place'       => $birthPlace,
            'birth_date'        => $birthDate,
            'mta_ayah_uuid'     => $ayahUuid,
            'mta_ibu_uuid'      => $ibuUuid,
            'mta_foto_url'      => $fotoUrl,
        ];

        DB::beginTransaction();
        try {
            $pemuda->update($updateData);

            $this->matchOrCreateAlamat($pemuda->id, $wargaData);
            $this->matchOrCreatePendidikan($pemuda->id, $wargaData);
            $this->matchOrCreatePekerjaan($pemuda->id, $wargaData);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("[MtaSyncService] syncSinglePemuda ID {$pemuda->id} error: " . $e->getMessage());
            return [
                'success' => false,
                'matched' => true,
                'message' => 'Gagal memperbarui data pemuda: ' . $e->getMessage(),
            ];
        }

        MtaSyncLog::log('pemuda_single', 'success', 1, "Pemuda '{$pemuda->name}' (#{$pemudaId}) berhasil disinkronkan & diverifikasi dengan data MTA.", $userId);

        return [
            'success'   => true,
            'matched'   => true,
            'pemuda_id' => $pemudaId,
            'warga'     => $wargaData,
            'message'   => "Seluruh data pemuda '{$pemuda->name}' cocok dan berhasil ditarik & diverifikasi dengan MTA Pusat!",
        ];
    }

    public function syncVerifyAll(?int $cabangId = null, bool $onlyPending = true, ?int $userId = null): array
    {
        $query = Pemuda::with('cabang')->where('status_data', 'active');

        if ($cabangId) {
            $query->where('cabang_id', $cabangId);
        }

        if ($onlyPending) {
            $query->where('status_verifikasi', 'pending');
        }

        $pemudaList   = $query->get();
        $total        = $pemudaList->count();
        $verifiedCount = 0;
        $unmatchedCount = 0;
        $failedCount   = 0;

        foreach ($pemudaList as $pemuda) {
            try {
                $result = $this->syncSinglePemuda($pemuda->id, $userId);
                if (($result['success'] ?? false) && ($result['matched'] ?? false)) {
                    $verifiedCount++;
                } else {
                    $unmatchedCount++;
                }
            } catch (\Throwable $e) {
                $failedCount++;
                Log::error("[MtaSyncService] Batch sync error ID {$pemuda->id}: " . $e->getMessage());
            }
        }

        $logMsg = "Batch verifikasi MTA selesai. Total: {$total}, Terverifikasi: {$verifiedCount}, Belum Cocok: {$unmatchedCount}, Gagal: {$failedCount}.";
        MtaSyncLog::log('pemuda_batch', 'success', $verifiedCount, $logMsg, $userId);

        return [
            'success'   => true,
            'total'     => $total,
            'verified'  => $verifiedCount,
            'unmatched' => $unmatchedCount,
            'failed'    => $failedCount,
            'message'   => $logMsg,
        ];
    }

    public function initSyncQueue(?int $cabangId = null, bool $onlyPending = true, ?int $userId = null, bool $reset = true): array
    {
        if ($reset) {
            MtaSyncQueue::truncate();
        }

        $query = Pemuda::where('status_data', 'active');
        if ($cabangId) {
            $query->where('cabang_id', $cabangId);
        }
        if ($onlyPending) {
            $query->where('status_verifikasi', 'pending');
        }

        $pemudaList = $query->get(['id', 'cabang_id']);

        if ($pemudaList->isEmpty()) {
            return [
                'success' => false,
                'message' => 'Tidak ada data pemuda yang memenuhi kriteria untuk disinkronkan.',
                'total'   => 0,
            ];
        }

        $insertData = [];
        $now = now();
        foreach ($pemudaList as $p) {
            $insertData[] = [
                'pemuda_id'  => $p->id,
                'cabang_id'  => $p->cabang_id,
                'status'     => 'pending',
                'created_by' => $userId ?? auth()->id(),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        MtaSyncQueue::insert($insertData);

        $total = count($insertData);
        $summary = MtaSyncQueue::getQueueSummary();

        MtaSyncLog::log('queue_init', 'success', $total, "Antrian verifikasi MTA disiapkan: {$total} data pemuda.", $userId);

        return [
            'success'             => true,
            'message'             => "Berhasil menyiapkan antrian verifikasi: {$total} data pemuda.",
            'total'               => $total,
            'estimated_time'      => $summary['estimated_formatted'],
            'estimated_seconds'   => $summary['estimated_seconds'],
            'summary'             => $summary,
        ];
    }

    public function processNextQueueItem(): array
    {
        // Pulihkan item processing yang macet (lebih dari 60 detik) kembali ke pending
        MtaSyncQueue::where('status', 'processing')
            ->where('processed_at', '<', now()->subSeconds(60))
            ->update(['status' => 'pending']);

        $queueItem = MtaSyncQueue::getNextPendingItem();

        if (!$queueItem) {
            $summary = MtaSyncQueue::getQueueSummary();
            return [
                'finished' => true,
                'message'  => 'Semua item dalam antrian telah selesai diproses.',
                'summary'  => $summary,
            ];
        }

        $queueItem->update([
            'status'       => 'processing',
            'processed_at' => now(),
        ]);

        $pemuda = $queueItem->pemuda;
        if (!$pemuda) {
            $queueItem->update([
                'status'  => 'failed',
                'result'  => 'error',
                'message' => 'Data pemuda tidak ditemukan.',
            ]);

            return [
                'finished' => false,
                'item'     => [
                    'id'             => $queueItem->id,
                    'pemuda_id'      => $queueItem->pemuda_id,
                    'name'           => '-',
                    'gender'         => '-',
                    'cabang_name'    => $queueItem->cabang?->name ?? '-',
                    'status'         => 'failed',
                    'result'         => 'error',
                    'message'        => 'Data pemuda tidak ditemukan.',
                    'mta_warga_uuid' => null,
                    'processed_at'   => now()->format('H:i:s'),
                ],
                'summary'  => MtaSyncQueue::getQueueSummary(),
            ];
        }

        try {
            $result = $this->syncSinglePemuda($pemuda->id, $queueItem->created_by);

            if (($result['success'] ?? false) && ($result['matched'] ?? false)) {
                $queueItem->update([
                    'status'         => 'completed',
                    'result'         => 'verified',
                    'message'        => 'Terverifikasi dengan MTA',
                    'mta_warga_uuid' => $result['warga']['uuid'] ?? null,
                ]);
            } else {
                $queueItem->update([
                    'status'  => 'completed',
                    'result'  => 'pending',
                    'message' => $result['message'] ?? 'Belum ditemukan data warga cocok di MTA',
                ]);
            }
        } catch (\Throwable $e) {
            $queueItem->update([
                'status'  => 'failed',
                'result'  => 'error',
                'message' => 'Error: ' . substr($e->getMessage(), 0, 200),
            ]);
        }

        $queueItem->refresh();

        return [
            'finished' => false,
            'item'     => [
                'id'             => $queueItem->id,
                'pemuda_id'      => $pemuda->id,
                'name'           => $pemuda->name,
                'gender'         => $pemuda->gender ?? '-',
                'cabang_name'    => $queueItem->cabang?->name ?? $pemuda->cabang?->name ?? '-',
                'status'         => $queueItem->status,
                'result'         => $queueItem->result,
                'message'        => $queueItem->message,
                'mta_warga_uuid' => $queueItem->mta_warga_uuid,
                'processed_at'   => $queueItem->processed_at ? $queueItem->processed_at->format('H:i:s') : now()->format('H:i:s'),
            ],
            'summary'  => MtaSyncQueue::getQueueSummary(),
        ];
    }

    public function getQueueStatus(): array
    {
        // Pulihkan item processing yang macet
        MtaSyncQueue::where('status', 'processing')
            ->where('processed_at', '<', now()->subSeconds(60))
            ->update(['status' => 'pending']);

        $recent = MtaSyncQueue::getRecentProcessed(20)->map(function ($item) {
            return [
                'id'             => $item->id,
                'pemuda_id'      => $item->pemuda_id,
                'name'           => $item->pemuda?->name ?? '-',
                'gender'         => $item->pemuda?->gender ?? '-',
                'cabang_name'    => $item->cabang?->name ?? $item->pemuda?->cabang?->name ?? '-',
                'status'         => $item->status,
                'result'         => $item->result,
                'message'        => $item->message,
                'mta_warga_uuid' => $item->mta_warga_uuid,
                'processed_at'   => $item->processed_at ? $item->processed_at->format('H:i:s') : '-',
            ];
        });

        return [
            'summary'          => MtaSyncQueue::getQueueSummary(),
            'recent_processed' => $recent->toArray(),
        ];
    }

    public function cancelQueue(): array
    {
        $canceled = MtaSyncQueue::whereIn('status', ['pending', 'processing'])->update([
            'status'  => 'failed',
            'result'  => 'failed',
            'message' => 'Dibatalkan oleh pengguna.',
        ]);

        return [
            'success' => true,
            'message' => "Antrian berhasil dibatalkan ({$canceled} data).",
        ];
    }

    public function matchMaritalStatus(?string $statusStr): string
    {
        $clean = strtolower(trim((string) $statusStr));
        if (str_contains($clean, 'belum')) {
            return 'belum_menikah';
        }
        if (str_contains($clean, 'duda')) {
            return 'duda';
        }
        if (str_contains($clean, 'janda')) {
            return 'janda';
        }
        if (str_contains($clean, 'nikah') || str_contains($clean, 'kawin') || str_contains($clean, 'sudah')) {
            return 'sudah_menikah';
        }
        return 'belum_menikah';
    }

    public function matchBloodType(?string $bloodStr): ?string
    {
        $clean = strtoupper(trim((string) $bloodStr));
        if (str_contains($clean, 'AB')) {
            return 'AB';
        }
        if (str_contains($clean, 'A')) {
            return 'A';
        }
        if (str_contains($clean, 'B')) {
            return 'B';
        }
        if (str_contains($clean, 'O')) {
            return 'O';
        }
        return null;
    }

    public function matchEducationLevel(?string $eduStr): int
    {
        $clean = strtolower(trim((string) $eduStr));
        if (empty($clean)) {
            return 3; // default SMA / SMK / MA
        }
        if (str_contains($clean, 's3') || str_contains($clean, 'doktor')) {
            return 7;
        }
        if (str_contains($clean, 's2') || str_contains($clean, 'magister')) {
            return 6;
        }
        if (str_contains($clean, 's1') || str_contains($clean, 'sarjana') || str_contains($clean, 'd4')) {
            return 5;
        }
        if (str_contains($clean, 'diploma') || str_contains($clean, 'd3') || str_contains($clean, 'd2') || str_contains($clean, 'd1') || str_contains($clean, 'akademi')) {
            return 4;
        }
        if (str_contains($clean, 'sma') || str_contains($clean, 'smk') || str_contains($clean, 'ma') || str_contains($clean, 'slta') || str_contains($clean, 'aliyah')) {
            return 3;
        }
        if (str_contains($clean, 'smp') || str_contains($clean, 'mts') || str_contains($clean, 'sltp') || str_contains($clean, 'tsanawiyah')) {
            return 2;
        }
        if (str_contains($clean, 'sd') || str_contains($clean, 'mi') || str_contains($clean, 'ibtidaiyah') || str_contains($clean, 'dasar')) {
            return 1;
        }
        return 3; // default SMA / SMK / MA
    }

    public function matchJobStatus(?string $jobStr): int
    {
        $clean = strtolower(trim((string) $jobStr));
        if (empty($clean)) {
            return 3; // default Karyawan Swasta
        }
        if (str_contains($clean, 'belum') || str_contains($clean, 'tidak bekerja') || str_contains($clean, 'menganggur') || str_contains($clean, 'ibu rumah tangga') || str_contains($clean, 'irt')) {
            return 1;
        }
        if (str_contains($clean, 'pelajar') || str_contains($clean, 'mahasiswa') || str_contains($clean, 'santri') || str_contains($clean, 'siswa')) {
            return 2;
        }
        if (str_contains($clean, 'pns') || str_contains($clean, 'asn') || str_contains($clean, 'pppk') || str_contains($clean, 'tni') || str_contains($clean, 'polri') || str_contains($clean, 'pamong') || str_contains($clean, 'aparatur') || str_contains($clean, 'pegawai negeri') || str_contains($clean, 'negeri') || str_contains($clean, 'sipil')) {
            return 4;
        }
        if (str_contains($clean, 'usaha') || str_contains($clean, 'wiraswasta') || str_contains($clean, 'pedagang') || str_contains($clean, 'bisnis') || str_contains($clean, 'dagang') || str_contains($clean, 'toko')) {
            return 5;
        }
        if (str_contains($clean, 'freelance') || str_contains($clean, 'lepas') || str_contains($clean, 'mandiri') || str_contains($clean, 'pekerja lepas')) {
            return 6;
        }
        if (str_contains($clean, 'petani') || str_contains($clean, 'tani') || str_contains($clean, 'peternak') || str_contains($clean, 'ternak') || str_contains($clean, 'kebun')) {
            return 7;
        }
        if (str_contains($clean, 'swasta') || str_contains($clean, 'karyawan') || str_contains($clean, 'buruh') || str_contains($clean, 'pegawai') || str_contains($clean, 'pabrik') || str_contains($clean, 'supir') || str_contains($clean, 'sopir') || str_contains($clean, 'driver') || str_contains($clean, 'mekanik') || str_contains($clean, 'teknisi') || str_contains($clean, 'security') || str_contains($clean, 'satpam') || str_contains($clean, 'staff')) {
            return 3;
        }
        return 8; // Lainnya
    }

    public function parseRtRw(?string $rtrwStr, ?string $alamatText = null): array
    {
        $rt = null;
        $rw = null;
        $combined = trim(($rtrwStr ?? '') . ' ' . ($alamatText ?? ''));

        if (!empty($combined)) {
            if (preg_match('/rt[\s\.\/:]*(\d+)/i', $combined, $mRt)) {
                $rt = str_pad($mRt[1], 2, '0', STR_PAD_LEFT);
            }
            if (preg_match('/rw[\s\.\/:]*(\d+)/i', $combined, $mRw)) {
                $rw = str_pad($mRw[1], 2, '0', STR_PAD_LEFT);
            }

            if (!$rt && !$rw && !empty($rtrwStr) && preg_match('/^\s*(\d+)\s*\/\s*(\d+)\s*$/', $rtrwStr, $mSlash)) {
                $rt = str_pad($mSlash[1], 2, '0', STR_PAD_LEFT);
                $rw = str_pad($mSlash[2], 2, '0', STR_PAD_LEFT);
            }
        }

        return ['rt' => $rt, 'rw' => $rw];
    }

    public function matchOrCreateAlamat(int $pemudaId, array $wargaData): void
    {
        $rtrw = $this->parseRtRw($wargaData['alamat_rtrw'] ?? null, $wargaData['alamat'] ?? null);
        $alamatText = trim($wargaData['alamat'] ?? ($wargaData['alamat_rtrw'] ?? ''));

        // Match Province
        $provinceId = 33; // Default Jawa Tengah
        if (!empty($wargaData['provinsi'])) {
            $cleanProv = trim($wargaData['provinsi']);
            $prov = Province::where('name', 'LIKE', '%' . $cleanProv . '%')->first();
            if ($prov) {
                $provinceId = $prov->id;
            }
        }

        // Match Regency
        $regencyId = 3314; // Default Kab. Sragen
        if (!empty($wargaData['kabupaten'])) {
            $cleanKab = trim(str_replace(['Kab.', 'Kabupaten', 'Kota'], '', $wargaData['kabupaten']));
            $reg = Regency::where('province_id', $provinceId)
                ->where('name', 'LIKE', '%' . $cleanKab . '%')
                ->first();
            if ($reg) {
                $regencyId = $reg->id;
            }
        }

        // Match District in Regency
        $districtId = null;
        if (!empty($wargaData['kecamatan'])) {
            $cleanKec = trim(str_replace('Kec.', '', $wargaData['kecamatan']));
            $dist = District::where('regency_id', $regencyId)
                ->where('name', 'LIKE', '%' . $cleanKec . '%')
                ->first();
            if ($dist) {
                $districtId = $dist->id;
            }
        }
        if (!$districtId) {
            $defaultDistrict = District::where('regency_id', $regencyId)->first();
            $districtId = $defaultDistrict ? $defaultDistrict->id : 1;
        }

        // Match Village in District
        $villageId = null;
        if (!empty($wargaData['desa'])) {
            $cleanDesa = trim(str_replace(['Desa', 'Kel.', 'Kelurahan'], '', $wargaData['desa']));
            $vill = Village::where('district_id', $districtId)
                ->where('name', 'LIKE', '%' . $cleanDesa . '%')
                ->first();
            if ($vill) {
                $villageId = $vill->id;
            }
        }
        if (!$villageId) {
            $defaultVillage = Village::where('district_id', $districtId)->first();
            $villageId = $defaultVillage ? $defaultVillage->id : 1;
        }

        $dusun = !empty($wargaData['desa']) ? trim($wargaData['desa']) : null;
        $addressDetail = !empty($alamatText) ? $alamatText : (!empty($wargaData['desa']) ? "Desa " . $wargaData['desa'] . ", Kec. " . ($wargaData['kecamatan'] ?? '') : 'Sragen');

        Alamat::updateOrCreate(
            ['pemuda_id' => $pemudaId],
            [
                'province_id'    => $provinceId,
                'regency_id'     => $regencyId,
                'district_id'    => $districtId,
                'village_id'     => $villageId,
                'dusun'          => $dusun,
                'rt'             => $rtrw['rt'],
                'rw'             => $rtrw['rw'],
                'address_detail' => $addressDetail,
            ]
        );
    }

    public function matchOrCreatePendidikan(int $pemudaId, array $wargaData): void
    {
        $eduLevelId = $this->matchEducationLevel($wargaData['pendidikan'] ?? '');
        $schoolName = !empty($wargaData['sekolah']) ? trim($wargaData['sekolah']) : '-';

        Pendidikan::updateOrCreate(
            ['pemuda_id' => $pemudaId],
            [
                'education_level_id' => $eduLevelId,
                'school_name'        => $schoolName,
                'education_status'   => 'lulus',
            ]
        );
    }

    public function matchOrCreatePekerjaan(int $pemudaId, array $wargaData): void
    {
        $jobStatusId = $this->matchJobStatus($wargaData['pekerjaan'] ?? '');
        $jobTitle = !empty($wargaData['pekerjaan']) ? trim($wargaData['pekerjaan']) : null;

        Pekerjaan::updateOrCreate(
            ['pemuda_id' => $pemudaId],
            [
                'job_status_id' => $jobStatusId,
                'job_title'     => $jobTitle,
            ]
        );
    }
}
