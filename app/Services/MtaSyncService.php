<?php

namespace App\Services;

use App\Models\Pemuda;
use App\Models\Wilayah;
use App\Models\Cabang;
use App\Models\Alamat;
use App\Models\Pendidikan;
use App\Models\Pekerjaan;
use App\Models\Organisasi;
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

        if (!isset($wargaData['alamat_rtrw']) && !empty($wargaUuid)) {
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

        $gender    = in_array(strtoupper($wargaData['kelamin'] ?? 'L'), ['L', 'P'], true) ? strtoupper($wargaData['kelamin']) : 'L';
        $birthDate = !empty($wargaData['lahir']) ? date('Y-m-d', strtotime($wargaData['lahir'])) : null;
        $phone     = !empty($wargaData['nohp']) ? preg_replace('/[^0-9+]/', '', $wargaData['nohp']) : null;
        $bloodType = !empty($wargaData['goldar']) ? strtoupper(trim($wargaData['goldar'])) : null;
        $mtaStatus = $wargaData['status'] ?? 'Warga';

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
                ];

                if (empty($existingPemuda->blood_type) && !empty($bloodType)) {
                    $updateData['blood_type'] = $bloodType;
                }
                if (empty($existingPemuda->phone) && !empty($phone)) {
                    $updateData['phone'] = $phone;
                }

                $existingPemuda->update($updateData);
                $pemudaId = $existingPemuda->id;
                $action   = 'updated';
            } else {
                $regNumber = Pemuda::generateRegistrationNumber($cabangId, $birthDate);

                $pemuda = Pemuda::create([
                    'cabang_id'           => $cabangId,
                    'registration_number' => $regNumber,
                    'name'                => $nama,
                    'gender'              => $gender,
                    'marital_status'      => 'belum_menikah',
                    'blood_type'          => $bloodType,
                    'birth_place'         => $wargaData['tempat_lahir'] ?? 'Sragen',
                    'birth_date'          => $birthDate ?: date('Y-m-d', strtotime('-20 years')),
                    'phone'               => $phone,
                    'email'               => !empty($wargaData['email']) ? trim($wargaData['email']) : null,
                    'status_verifikasi'   => 'verified',
                    'status_data'         => 'active',
                    'mta_warga_uuid'      => $wargaUuid,
                    'mta_status_warga'    => $mtaStatus,
                    'mta_synced_at'       => now(),
                    'created_by'          => $userId ?? auth()->id(),
                ]);

                $pemudaId = $pemuda->id;
                $action   = 'created';

                $this->matchOrCreateAlamat($pemudaId, $wargaData);

                Pendidikan::create([
                    'pemuda_id'          => $pemudaId,
                    'education_level_id' => $this->matchEducationLevel($wargaData['pendidikan'] ?? ''),
                    'school_name'        => !empty($wargaData['sekolah']) ? $wargaData['sekolah'] : '-',
                    'education_status'   => 'lulus',
                ]);

                Pekerjaan::create([
                    'pemuda_id'     => $pemudaId,
                    'job_status_id' => $this->matchJobStatus($wargaData['pekerjaan'] ?? ''),
                    'job_title'     => !empty($wargaData['pekerjaan']) ? $wargaData['pekerjaan'] : null,
                ]);
            }

            DB::commit();

            return [
                'success'   => true,
                'action'    => $action,
                'pemuda_id' => $pemudaId,
                'name'      => $nama,
                'message'   => "Data pemuda '{$nama}' berhasil disinkronkan ({$action}).",
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
        $pemuda = Pemuda::with('cabang')->find($pemudaId);
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

        $updateData = [
            'mta_warga_uuid'    => $wargaData['uuid'] ?? $pemuda->mta_warga_uuid,
            'mta_status_warga'  => $wargaData['status'] ?? ($pemuda->mta_status_warga ?: 'Warga'),
            'status_verifikasi' => 'verified',
            'mta_synced_at'     => now(),
        ];

        if (empty($pemuda->blood_type) && !empty($wargaData['goldar'])) {
            $updateData['blood_type'] = strtoupper(trim($wargaData['goldar']));
        }
        if (empty($pemuda->phone) && !empty($wargaData['nohp'])) {
            $updateData['phone'] = preg_replace('/[^0-9+]/', '', $wargaData['nohp']);
        }

        $pemuda->update($updateData);

        MtaSyncLog::log('pemuda_single', 'success', 1, "Pemuda '{$pemuda->name}' (#{$pemudaId}) berhasil diverifikasi dengan data MTA.", $userId);

        return [
            'success'   => true,
            'matched'   => true,
            'pemuda_id' => $pemudaId,
            'warga'     => $wargaData,
            'message'   => "Data pemuda '{$pemuda->name}' cocok dan berhasil diverifikasi dengan MTA!",
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
                'result'  => 'failed',
                'message' => 'Data pemuda tidak ditemukan.',
            ]);

            return [
                'finished' => false,
                'item'     => $queueItem->toArray(),
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
                'result'  => 'failed',
                'message' => 'Error: ' . substr($e->getMessage(), 0, 200),
            ]);
        }

        return [
            'finished' => false,
            'item'     => array_merge($queueItem->toArray(), [
                'name'      => $pemuda->name,
                'pemuda_id' => $pemuda->id,
            ]),
            'summary'  => MtaSyncQueue::getQueueSummary(),
        ];
    }

    public function getQueueStatus(): array
    {
        return [
            'summary'          => MtaSyncQueue::getQueueSummary(),
            'recent_processed' => MtaSyncQueue::getRecentProcessed(15)->toArray(),
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

    protected function matchEducationLevel(string $eduStr): int
    {
        $clean = strtolower(trim($eduStr));
        if (str_contains($clean, 's3') || str_contains($clean, 'doktor')) return 9;
        if (str_contains($clean, 's2') || str_contains($clean, 'magister')) return 8;
        if (str_contains($clean, 's1') || str_contains($clean, 'sarjana') || str_contains($clean, 'd4')) return 7;
        if (str_contains($clean, 'd3') || str_contains($clean, 'diploma 3')) return 6;
        if (str_contains($clean, 'd2') || str_contains($clean, 'd1')) return 5;
        if (str_contains($clean, 'sma') || str_contains($clean, 'smk') || str_contains($clean, 'ma') || str_contains($clean, 'slta')) return 4;
        if (str_contains($clean, 'smp') || str_contains($clean, 'mts') || str_contains($clean, 'sltp')) return 3;
        if (str_contains($clean, 'sd') || str_contains($clean, 'mi')) return 2;
        return 4; // default SMA/SMK
    }

    protected function matchJobStatus(string $jobStr): int
    {
        $clean = strtolower(trim($jobStr));
        if (str_contains($clean, 'belum') || str_contains($clean, 'tidak bekerja') || str_contains($clean, 'menganggur')) return 5;
        if (str_contains($clean, 'usaha') || str_contains($clean, 'wiraswasta') || str_contains($clean, 'pedagang') || str_contains($clean, 'bisnis')) return 2;
        if (str_contains($clean, 'pns') || str_contains($clean, 'asn') || str_contains($clean, 'tni') || str_contains($clean, 'polri')) return 3;
        if (str_contains($clean, 'swasta') || str_contains($clean, 'karyawan') || str_contains($clean, 'buruh') || str_contains($clean, 'pegawai')) return 1;
        if (str_contains($clean, 'pelajar') || str_contains($clean, 'mahasiswa')) return 4;
        return 1; // default Karyawan Swasta
    }

    protected function matchOrCreateAlamat(int $pemudaId, array $wargaData): void
    {
        $alamatText = $wargaData['alamat'] ?? ($wargaData['alamat_rtrw'] ?? '');
        $rt = null;
        $rw = null;
        if (preg_match('/rt\s*[:\.\/]?\s*(\d+)/i', $alamatText, $mRt)) {
            $rt = str_pad($mRt[1], 2, '0', STR_PAD_LEFT);
        }
        if (preg_match('/rw\s*[:\.\/]?\s*(\d+)/i', $alamatText, $mRw)) {
            $rw = str_pad($mRw[1], 2, '0', STR_PAD_LEFT);
        }

        $defaultDistrict = District::where('regency_id', 3314)->first();
        $districtId = $defaultDistrict ? $defaultDistrict->id : 1;

        $defaultVillage = Village::where('district_id', $districtId)->first();
        $villageId = $defaultVillage ? $defaultVillage->id : 1;

        Alamat::create([
            'pemuda_id'      => $pemudaId,
            'province_id'    => 33, // Jawa Tengah
            'regency_id'     => 3314, // Sragen
            'district_id'    => $districtId,
            'village_id'     => $villageId,
            'rt'             => $rt,
            'rw'             => $rw,
            'address_detail' => !empty($alamatText) ? $alamatText : 'Sragen',
        ]);
    }
}
