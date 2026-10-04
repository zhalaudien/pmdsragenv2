<?php

namespace App\Services;

use App\Models\Cabang;
use App\Models\GdmPenugasan;
use App\Models\GuruDaerahMuda;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GdmCabangSyncService
{
    protected ?MtaApiService $mtaApiService;

    public function __construct(?MtaApiService $mtaApiService = null)
    {
        $this->mtaApiService = $mtaApiService ?: app(MtaApiService::class);
    }

    /**
     * Melakukan sinkronisasi menyeluruh dua arah antara Penugasan GDM dan Master Cabang,
     * serta sinkronisasi alamat domisili GDM dari data Pemuda / Warga MTA.
     *
     * 1. Dari GdmPenugasan Aktif -> Cabang:
     *    - Mengeset has_gelombang = 'sudah' pada Cabang sasaran.
     *    - Mengisi gelombang_ustadz pada Cabang sesuai nama GDM bertugas.
     *    - Menyelaraskan hari_kajian dan jam_kajian antara penugasan dan cabang jika salah satunya kosong.
     *
     * 2. Dari Cabang -> GdmPenugasan:
     *    - Jika Cabang memiliki gelombang_ustadz yang cocok dengan nama kader GDM aktif namun belum
     *      memiliki record penugasan aktif di cabang tersebut, sistem otomatis membuatkan penugasan baru.
     *
     * 3. Alamat Domisili GDM -> Pemuda / Warga MTA:
     *    - Menyelaraskan field alamat domisili GDM dengan data alamat lengkap Pemuda atau Warga MTA.
     *
     * @param int|null $tahun Tahun penugasan yang disinkronkan (default: tahun berjalan)
     * @param bool $syncAlamat Apakah menyinkronkan alamat domisili GDM sekaligus
     * @return array Ringkasan hasil sinkronisasi
     */
    public function syncAll(?int $tahun = null, bool $syncAlamat = true): array
    {
        $targetYear = $tahun ?: (int) date('Y');

        $syncedCabangCount    = 0;
        $syncedPenugasanCount = 0;
        $createdPenugasanCount = 0;
        $syncedAlamatCount    = 0;
        $logs = [];

        DB::beginTransaction();
        try {
            // -------------------------------------------------------------
            // TAHAP 1: Sinkronisasi dari GdmPenugasan Aktif ke Master Cabang
            // -------------------------------------------------------------
            $activePenugasans = GdmPenugasan::with(['gdm', 'cabang'])
                ->where('status', 'aktif')
                ->where('tahun', $targetYear)
                ->get();

            // Kelompokkan penugasan berdasarkan cabang_id
            $penugasanByCabang = $activePenugasans->groupBy('cabang_id');

            foreach ($penugasanByCabang as $cabangId => $penugasans) {
                $cabang = Cabang::find($cabangId);
                if (!$cabang) {
                    continue;
                }

                $gdmNames = $penugasans->map(fn($p) => $p->gdm?->nama)
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                $gdmNamesStr = implode(', ', $gdmNames);

                $cabangChanged = false;

                // 1. Pastikan has_gelombang = 'sudah'
                if ($cabang->has_gelombang !== 'sudah') {
                    $cabang->has_gelombang = 'sudah';
                    $cabangChanged = true;
                }

                // 2. Sinkronkan nama ustadz pengampu jika kosong atau belum memuat nama GDM
                if (!empty($gdmNamesStr) && $cabang->gelombang_ustadz !== $gdmNamesStr) {
                    $cabang->gelombang_ustadz = $gdmNamesStr;
                    $cabangChanged = true;
                }

                // 3. Sinkronkan jadwal (hari & jam) dua arah
                $firstPenugasan = $penugasans->first();
                if ($firstPenugasan) {
                    $penugasanChanged = false;

                    // Hari kajian
                    if (empty($firstPenugasan->hari_kajian) && !empty($cabang->gelombang_hari)) {
                        $firstPenugasan->hari_kajian = $cabang->gelombang_hari;
                        $penugasanChanged = true;
                    } elseif (!empty($firstPenugasan->hari_kajian) && empty($cabang->gelombang_hari)) {
                        $cabang->gelombang_hari = $firstPenugasan->hari_kajian;
                        $cabangChanged = true;
                    }

                    // Jam kajian
                    if (empty($firstPenugasan->jam_kajian) && !empty($cabang->gelombang_jam)) {
                        $firstPenugasan->jam_kajian = $cabang->gelombang_jam;
                        $penugasanChanged = true;
                    } elseif (!empty($firstPenugasan->jam_kajian) && empty($cabang->gelombang_jam)) {
                        $cabang->gelombang_jam = $firstPenugasan->jam_kajian;
                        $cabangChanged = true;
                    }

                    if ($penugasanChanged) {
                        $firstPenugasan->save();
                        $syncedPenugasanCount++;
                    }
                }

                if ($cabangChanged) {
                    $cabang->save();
                    $syncedCabangCount++;
                    $logs[] = "Cabang {$cabang->name}: jadwal & ustadz disinkronkan dengan GDM ({$gdmNamesStr}).";
                }
            }

            // -------------------------------------------------------------
            // TAHAP 2: Cek Master Cabang dengan gelombang_ustadz terisi
            //          untuk mencocokkan ke kader GDM
            // -------------------------------------------------------------
            $allActiveGdm = GuruDaerahMuda::where('status', 'aktif')->get();

            if ($allActiveGdm->isNotEmpty()) {
                $cabangsWithUstadz = Cabang::where('has_gelombang', 'sudah')
                    ->whereNotNull('gelombang_ustadz')
                    ->where('gelombang_ustadz', '!=', '')
                    ->get();

                foreach ($cabangsWithUstadz as $c) {
                    $cleanUstadzName = $this->cleanUstadzName($c->gelombang_ustadz);

                    if (mb_strlen($cleanUstadzName) < 3) {
                        continue;
                    }

                    // Cari GDM yang namanya cocok
                    $matchedGdm = $allActiveGdm->first(function ($g) use ($cleanUstadzName) {
                        $gdmClean = $this->cleanUstadzName($g->nama);
                        return strtolower($gdmClean) === strtolower($cleanUstadzName)
                            || stripos($gdmClean, $cleanUstadzName) !== false
                            || stripos($cleanUstadzName, $gdmClean) !== false;
                    });

                    if ($matchedGdm) {
                        // Cek apakah sudah ada penugasan aktif di cabang ini untuk tahun target
                        $existingPenugasan = GdmPenugasan::where('gdm_id', $matchedGdm->id)
                            ->where('cabang_id', $c->id)
                            ->where('tahun', $targetYear)
                            ->first();

                        if (!$existingPenugasan) {
                            GdmPenugasan::create([
                                'gdm_id'      => $matchedGdm->id,
                                'tahun'       => $targetYear,
                                'cabang_id'   => $c->id,
                                'hari_kajian' => $c->gelombang_hari,
                                'jam_kajian'  => $c->gelombang_jam,
                                'status'      => 'aktif',
                                'keterangan'  => 'Sinkronisasi otomatis dari Master Cabang',
                            ]);

                            $createdPenugasanCount++;
                            $logs[] = "Dibuat penugasan baru: {$matchedGdm->nama} ditugaskan di Cabang {$c->name} ({$targetYear}).";
                        }
                    }
                }
            }

            DB::commit();

            // -------------------------------------------------------------
            // TAHAP 3: Sinkronisasi Alamat Domisili GDM dari Pemuda / Warga
            // -------------------------------------------------------------
            if ($syncAlamat) {
                $alamatResult = $this->syncAllAlamat();
                $syncedAlamatCount = $alamatResult['synced_count'] ?? 0;
                if (!empty($alamatResult['logs'])) {
                    $logs = array_merge($logs, $alamatResult['logs']);
                }
            }

            return [
                'status'                 => 'success',
                'tahun'                  => $targetYear,
                'synced_cabang_count'    => $syncedCabangCount,
                'synced_penugasan_count' => $syncedPenugasanCount,
                'created_penugasan_count'=> $createdPenugasanCount,
                'synced_alamat_count'    => $syncedAlamatCount,
                'logs'                   => $logs,
                'message'                => "Sinkronisasi berhasil: {$syncedCabangCount} cabang, " . ($syncedPenugasanCount + $createdPenugasanCount) . " penugasan GDM, dan {$syncedAlamatCount} alamat domisili GDM telah diselaraskan.",
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error saat sinkronisasi GDM dan Master Cabang: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'status'  => 'error',
                'message' => 'Gagal menyinkronkan data: ' . $e->getMessage(),
                'logs'    => [],
            ];
        }
    }

    /**
     * Sinkronkan alamat domisili seluruh kader Guru Daerah Muda dari sumber datanya (Data Pemuda atau Warga MTA).
     *
     * @return array
     */
    public function syncAllAlamat(): array
    {
        $allGdm = GuruDaerahMuda::with(['pemuda.alamat.village', 'pemuda.alamat.district', 'pemuda.alamat.regency'])->get();
        $syncedCount = 0;
        $logs = [];

        foreach ($allGdm as $gdm) {
            try {
                $changed = $gdm->syncAlamatFromSource($this->mtaApiService);
                if ($changed) {
                    $syncedCount++;
                    $logs[] = "Alamat GDM {$gdm->nama} diselaraskan: {$gdm->alamat}";
                }
            } catch (\Throwable $e) {
                Log::warning("Gagal menyinkronkan alamat GDM ID {$gdm->id} ({$gdm->nama}): " . $e->getMessage());
            }
        }

        return [
            'status'       => 'success',
            'synced_count' => $syncedCount,
            'total_gdm'    => $allGdm->count(),
            'logs'         => $logs,
            'message'      => "Sinkronisasi alamat selesai: {$syncedCount} dari {$allGdm->count()} kader GDM berhasil diselaraskan dengan data domisili Pemuda / Warga MTA.",
        ];
    }

    /**
     * Sinkronkan satu record penugasan GDM ke target Cabang.
     */
    public function syncPenugasanToCabang(GdmPenugasan $penugasan): bool
    {
        $cabang = Cabang::find($penugasan->cabang_id);
        if (!$cabang) {
            return false;
        }

        $penugasan->loadMissing('gdm');

        if ($penugasan->status === 'aktif') {
            $cabangChanged = false;

            if ($cabang->has_gelombang !== 'sudah') {
                $cabang->has_gelombang = 'sudah';
                $cabangChanged = true;
            }

            // Update nama ustadz pengampu di cabang
            if ($penugasan->gdm && $penugasan->gdm->nama) {
                // Ambil semua nama GDM aktif di cabang ini
                $activeNames = GdmPenugasan::where('cabang_id', $cabang->id)
                    ->where('status', 'aktif')
                    ->where('id', '!=', $penugasan->id)
                    ->with('gdm')
                    ->get()
                    ->map(fn($p) => $p->gdm?->nama)
                    ->filter()
                    ->all();

                $activeNames[] = $penugasan->gdm->nama;
                $activeNames = array_values(array_unique($activeNames));
                $joinedNames = implode(', ', $activeNames);

                if ($cabang->gelombang_ustadz !== $joinedNames) {
                    $cabang->gelombang_ustadz = $joinedNames;
                    $cabangChanged = true;
                }
            }

            // Selaraskan hari kajian
            if (!empty($penugasan->hari_kajian) && $cabang->gelombang_hari !== $penugasan->hari_kajian) {
                $cabang->gelombang_hari = $penugasan->hari_kajian;
                $cabangChanged = true;
            } elseif (empty($penugasan->hari_kajian) && !empty($cabang->gelombang_hari)) {
                $penugasan->hari_kajian = $cabang->gelombang_hari;
                $penugasan->save();
            }

            // Selaraskan jam kajian
            if (!empty($penugasan->jam_kajian) && $cabang->gelombang_jam !== $penugasan->jam_kajian) {
                $cabang->gelombang_jam = $penugasan->jam_kajian;
                $cabangChanged = true;
            } elseif (empty($penugasan->jam_kajian) && !empty($cabang->gelombang_jam)) {
                $penugasan->jam_kajian = $cabang->gelombang_jam;
                $penugasan->save();
            }

            if ($cabangChanged) {
                $cabang->save();
            }

            return true;
        }

        // Jika status penugasan selesai atau ditarik, refresh ustadz cabang
        $this->refreshCabangUstadz($cabang);
        return true;
    }

    /**
     * Merefresh field gelombang_ustadz pada Cabang berdasarkan penugasan GDM aktif yang tersisa.
     */
    public function refreshCabangUstadz(Cabang $cabang): void
    {
        $activePenugasans = GdmPenugasan::with('gdm')
            ->where('cabang_id', $cabang->id)
            ->where('status', 'aktif')
            ->get();

        if ($activePenugasans->isNotEmpty()) {
            $gdmNames = $activePenugasans->map(fn($p) => $p->gdm?->nama)
                ->filter()
                ->unique()
                ->values()
                ->all();

            $cabang->gelombang_ustadz = implode(', ', $gdmNames);
            $cabang->has_gelombang = 'sudah';
            $cabang->save();
        } else {
            // Jika tidak ada lagi GDM aktif bertugas di cabang ini
            // Jika gelombang_ustadz cocok dengan salah satu nama GDM, kita kosongkan
            $allGdmNames = GuruDaerahMuda::pluck('nama')->toArray();
            $currentUstadz = trim((string) $cabang->gelombang_ustadz);

            $matched = false;
            foreach ($allGdmNames as $gName) {
                if (!empty($currentUstadz) && (stripos($currentUstadz, $gName) !== false || stripos($gName, $currentUstadz) !== false)) {
                    $matched = true;
                    break;
                }
            }

            if ($matched) {
                $cabang->gelombang_ustadz = null;
                $cabang->save();
            }
        }
    }

    /**
     * Sinkronkan data dari Cabang (misal ketika pengurus cabang menyimpan ustadz atau memilih GDM).
     */
    public function syncCabangToPenugasan(Cabang $cabang, ?int $gdmId = null): void
    {
        $currentYear = (int) date('Y');

        if ($gdmId) {
            $gdm = GuruDaerahMuda::find($gdmId);
            if ($gdm) {
                // Pastikan penugasan aktif ada
                $penugasan = GdmPenugasan::where('gdm_id', $gdm->id)
                    ->where('cabang_id', $cabang->id)
                    ->where('tahun', $currentYear)
                    ->first();

                if (!$penugasan) {
                    GdmPenugasan::create([
                        'gdm_id'      => $gdm->id,
                        'tahun'       => $currentYear,
                        'cabang_id'   => $cabang->id,
                        'hari_kajian' => $cabang->gelombang_hari,
                        'jam_kajian'  => $cabang->gelombang_jam,
                        'status'      => 'aktif',
                        'keterangan'  => 'Ditugaskan melalui Master Cabang',
                    ]);
                } else {
                    $penugasan->update([
                        'status'      => 'aktif',
                        'hari_kajian' => $cabang->gelombang_hari ?: $penugasan->hari_kajian,
                        'jam_kajian'  => $cabang->gelombang_jam ?: $penugasan->jam_kajian,
                    ]);
                }

                if ($cabang->gelombang_ustadz !== $gdm->nama) {
                    $cabang->gelombang_ustadz = $gdm->nama;
                    $cabang->save();
                }
                return;
            }
        }

        // Jika tidak ada gdmId, periksa apakah ada penugasan aktif di cabang ini untuk menyelaraskan hari & jam
        $activePenugasans = GdmPenugasan::where('cabang_id', $cabang->id)
            ->where('status', 'aktif')
            ->get();

        foreach ($activePenugasans as $p) {
            $p->update([
                'hari_kajian' => $cabang->gelombang_hari ?: $p->hari_kajian,
                'jam_kajian'  => $cabang->gelombang_jam ?: $p->jam_kajian,
            ]);
        }
    }

    /**
     * Membersihkan gelar / titel ustadz dari string nama untuk keperluan pencocokan nama.
     */
    protected function cleanUstadzName(?string $name): string
    {
        if (empty($name)) {
            return '';
        }

        // Hapus titel ustadz di depan (contoh: Ust. Ahmad, Ustadz Fauzi, Ustd. Budi)
        $clean = preg_replace('/^(ust\.|ustadz\.|ustadz|ustd\.|ustd|ust\b)\s*/i', '', trim($name));
        // Hilangkan whitespace ganda
        $clean = preg_replace('/\s+/', ' ', $clean);

        return trim($clean);
    }
}
