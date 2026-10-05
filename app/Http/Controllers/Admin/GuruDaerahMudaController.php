<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cabang;
use App\Models\GdmPenugasan;
use App\Models\GuruDaerahMuda;
use App\Models\Pemuda;
use App\Models\Wilayah;
use App\Services\GdmCabangSyncService;
use App\Services\MtaApiService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GuruDaerahMudaController extends Controller
{
    protected MtaApiService $apiService;

    public function __construct(MtaApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * Dashboard & Daftar Manajemen Guru Daerah Muda (GDM)
     */
    public function index(Request $request)
    {
        $search             = trim((string) $request->input('search'));
        $cabangAsalId       = $request->input('cabang_asal_id');
        $cabangPenugasanId  = $request->input('cabang_penugasan_id');
        $tahunPenugasan     = $request->input('tahun');
        $status             = $request->input('status');
        $sumberData         = $request->input('sumber_data');
        $perPage            = (int) ($request->input('per_page', 15));
        $perPage            = in_array($perPage, [10, 15, 25, 50, 100], true) ? $perPage : 15;

        $query = GuruDaerahMuda::with([
            'cabang.wilayah',
            'pemuda',
            'penugasan.cabang',
        ]);

        if ($search !== '') {
            $s = '%' . $search . '%';
            $query->where(function ($q) use ($s) {
                $q->where('nama', 'LIKE', $s)
                  ->orWhere('tempat_lahir', 'LIKE', $s)
                  ->orWhere('alamat', 'LIKE', $s)
                  ->orWhere('no_wa', 'LIKE', $s)
                  ->orWhere('catatan', 'LIKE', $s);
            });
        }

        if (!empty($cabangAsalId)) {
            $query->where('cabang_id', (int) $cabangAsalId);
        }

        if (!empty($cabangPenugasanId)) {
            $query->whereHas('penugasan', function ($q) use ($cabangPenugasanId) {
                $q->where('cabang_id', (int) $cabangPenugasanId);
            });
        }

        if (!empty($tahunPenugasan)) {
            $query->whereHas('penugasan', function ($q) use ($tahunPenugasan) {
                $q->where('tahun', (int) $tahunPenugasan);
            });
        }

        if (!empty($status) && in_array($status, ['aktif', 'nonaktif'], true)) {
            $query->where('status', $status);
        }

        if (!empty($sumberData) && in_array($sumberData, ['pemuda', 'warga', 'manual'], true)) {
            $query->where('sumber_data', $sumberData);
        }

        $gdmList = $query->orderBy('nama', 'ASC')
            ->paginate($perPage)
            ->withQueryString();

        // KPI Statistics
        $totalGdm            = GuruDaerahMuda::count();
        $totalGdmAktif       = GuruDaerahMuda::where('status', 'aktif')->count();
        $totalPenugasanAktif = GdmPenugasan::where('status', 'aktif')->count();
        $totalCabangSasaran  = GdmPenugasan::distinct('cabang_id')->count('cabang_id');
        $totalRiwayat        = GdmPenugasan::count();

        // Reference Data
        $cabangList  = Cabang::with('wilayah')->orderBy('name', 'ASC')->get();
        $wilayahList = Wilayah::orderBy('name', 'ASC')->get();

        $existingTahun = GdmPenugasan::distinct()->orderBy('tahun', 'desc')->pluck('tahun')->toArray();
        $currentYear = (int) date('Y');
        if (!in_array($currentYear, $existingTahun, true)) {
            array_unshift($existingTahun, $currentYear);
        }

        return view('admin.gdm.index', [
            'title'               => 'Manajemen Guru Daerah Muda (GDM)',
            'gdmList'             => $gdmList,
            'cabangList'          => $cabangList,
            'wilayahList'         => $wilayahList,
            'tahunList'           => $existingTahun,
            'totalGdm'            => $totalGdm,
            'totalGdmAktif'       => $totalGdmAktif,
            'totalPenugasanAktif' => $totalPenugasanAktif,
            'totalCabangSasaran'  => $totalCabangSasaran,
            'totalRiwayat'        => $totalRiwayat,
            'search'              => $search,
            'selectedCabangAsal'  => $cabangAsalId,
            'selectedCabangTugas' => $cabangPenugasanId,
            'selectedTahun'       => $tahunPenugasan,
            'selectedStatus'      => $status,
            'selectedSumber'      => $sumberData,
            'user'                => session()->all(),
        ]);
    }

    /**
     * Detail GDM beserta riwayat penugasan kajian
     */
    public function detail(int $id): JsonResponse
    {
        $gdm = GuruDaerahMuda::with([
            'cabang.wilayah',
            'pemuda.cabang',
            'penugasan.cabang.wilayah',
        ])->find($id);

        if (!$gdm) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Data Guru Daerah Muda tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data'   => [
                'id'             => $gdm->id,
                'nama'           => $gdm->nama,
                'tempat_lahir'   => $gdm->tempat_lahir,
                'tanggal_lahir'  => $gdm->tanggal_lahir ? $gdm->tanggal_lahir->format('Y-m-d') : null,
                'tanggal_lahir_formatted' => $gdm->tanggal_lahir ? Carbon::parse($gdm->tanggal_lahir)->translatedFormat('d F Y') : null,
                'ttl'            => $gdm->ttl,
                'usia'           => $gdm->usia,
                'cabang_id'      => $gdm->cabang_id,
                'cabang_name'    => $gdm->cabang?->name,
                'wilayah_name'   => $gdm->cabang?->wilayah?->name,
                'alamat'              => $gdm->alamat,
                'sumber_alamat_label' => $gdm->sumber_alamat_label,
                'pemuda_name'         => $gdm->pemuda?->name,
                'no_wa'               => $gdm->no_wa,
                'wa_link'             => $gdm->wa_link,
                'status'              => $gdm->status,
                'sumber_data'         => $gdm->sumber_data,
                'pemuda_id'           => $gdm->pemuda_id,
                'mta_warga_uuid'      => $gdm->mta_warga_uuid,
                'catatan'             => $gdm->catatan,
                'created_at'     => $gdm->created_at?->translatedFormat('d M Y H:i'),
                'penugasan'      => $gdm->penugasan->map(fn($p) => [
                    'id'           => $p->id,
                    'tahun'        => $p->tahun,
                    'cabang_id'    => $p->cabang_id,
                    'cabang_name'  => $p->cabang?->name ?? 'Cabang Tidak Ditemukan',
                    'wilayah_name' => $p->cabang?->wilayah?->name ?? '-',
                    'hari_kajian'  => $p->hari_kajian ?: ($p->cabang?->gelombang_hari ?: '-'),
                    'jam_kajian'   => $p->jam_kajian ?: ($p->cabang?->gelombang_jam ?: '-'),
                    'status'       => $p->status,
                    'keterangan'   => $p->keterangan,
                    'created_at'   => $p->created_at?->format('d/m/Y'),
                ]),
            ],
        ]);
    }

    /**
     * Simpan data GDM baru (beserta penugasan awal jika diisi)
     */
    public function simpan(Request $request)
    {
        $request->validate([
            'nama'                 => 'required|min:2|max:150',
            'tempat_lahir'         => 'nullable|max:100',
            'tanggal_lahir'        => 'nullable|date',
            'cabang_id'            => 'nullable|integer|exists:cabang,id',
            'alamat'               => 'nullable',
            'no_wa'                => 'nullable|max:25',
            'status'               => 'required|in:aktif,nonaktif',
            'sumber_data'          => 'required|in:pemuda,warga,manual',
            'pemuda_id'            => 'nullable|integer|exists:pemuda,id',
            'mta_warga_uuid'       => 'nullable|max:36',
            'catatan'              => 'nullable',
            // Penugasan awal (opsional)
            'penugasan_tahun'      => 'nullable|integer|min:2000|max:2100',
            'penugasan_cabang_id'  => 'nullable|integer|exists:cabang,id',
            'penugasan_hari'       => 'nullable|max:50',
            'penugasan_jam'        => 'nullable|max:50',
            'penugasan_status'     => 'nullable|in:aktif,selesai,ditarik',
            'penugasan_keterangan' => 'nullable',
        ]);

        DB::beginTransaction();
        try {
            $gdm = GuruDaerahMuda::create([
                'nama'           => trim($request->input('nama')),
                'tempat_lahir'   => $request->input('tempat_lahir') ? trim($request->input('tempat_lahir')) : null,
                'tanggal_lahir'  => $request->input('tanggal_lahir'),
                'cabang_id'      => $request->input('cabang_id'),
                'alamat'         => $request->input('alamat') ? trim($request->input('alamat')) : null,
                'no_wa'          => $request->input('no_wa') ? trim($request->input('no_wa')) : null,
                'status'         => $request->input('status', 'aktif'),
                'sumber_data'    => $request->input('sumber_data', 'manual'),
                'pemuda_id'      => $request->input('pemuda_id'),
                'mta_warga_uuid' => $request->input('mta_warga_uuid'),
                'catatan'        => $request->input('catatan'),
            ]);

            // Jika ada penugasan awal
            if ($request->filled('penugasan_cabang_id') && $request->filled('penugasan_tahun')) {
                $penugasan = GdmPenugasan::create([
                    'gdm_id'      => $gdm->id,
                    'tahun'       => (int) $request->input('penugasan_tahun'),
                    'cabang_id'   => (int) $request->input('penugasan_cabang_id'),
                    'hari_kajian' => $request->input('penugasan_hari'),
                    'jam_kajian'  => $request->input('penugasan_jam'),
                    'status'      => $request->input('penugasan_status', 'aktif'),
                    'keterangan'  => $request->input('penugasan_keterangan'),
                ]);

                app(GdmCabangSyncService::class)->syncPenugasanToCabang($penugasan);
            }

            // Sinkronkan alamat domisili jika alamat masih kosong dan terhubung ke Pemuda / Warga MTA
            if (empty($gdm->alamat) && ($gdm->pemuda_id || !empty($gdm->mta_warga_uuid))) {
                $gdm->syncAlamatFromSource($this->apiService);
            }

            DB::commit();

            return redirect()->route('admin.gdm.index')
                ->with('success', "Guru Daerah Muda {$gdm->nama} berhasil ditambahkan.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Gagal menyimpan data GDM: ' . $e->getMessage());
        }
    }

    /**
     * Perbarui data Guru Daerah Muda
     */
    public function update(Request $request, int $id)
    {
        $gdm = GuruDaerahMuda::findOrFail($id);

        $request->validate([
            'nama'           => 'required|min:2|max:150',
            'tempat_lahir'   => 'nullable|max:100',
            'tanggal_lahir'  => 'nullable|date',
            'cabang_id'      => 'nullable|integer|exists:cabang,id',
            'alamat'         => 'nullable',
            'no_wa'          => 'nullable|max:25',
            'status'         => 'required|in:aktif,nonaktif',
            'sumber_data'    => 'required|in:pemuda,warga,manual',
            'pemuda_id'      => 'nullable|integer|exists:pemuda,id',
            'mta_warga_uuid' => 'nullable|max:36',
            'catatan'        => 'nullable',
        ]);

        $gdm->update([
            'nama'           => trim($request->input('nama')),
            'tempat_lahir'   => $request->input('tempat_lahir') ? trim($request->input('tempat_lahir')) : null,
            'tanggal_lahir'  => $request->input('tanggal_lahir'),
            'cabang_id'      => $request->input('cabang_id'),
            'alamat'         => $request->input('alamat') ? trim($request->input('alamat')) : null,
            'no_wa'          => $request->input('no_wa') ? trim($request->input('no_wa')) : null,
            'status'         => $request->input('status', 'aktif'),
            'sumber_data'    => $request->input('sumber_data', $gdm->sumber_data),
            'pemuda_id'      => $request->input('pemuda_id', $gdm->pemuda_id),
            'mta_warga_uuid' => $request->input('mta_warga_uuid', $gdm->mta_warga_uuid),
            'catatan'        => $request->input('catatan'),
        ]);

        // Sinkronkan alamat domisili jika alamat masih kosong dan terhubung ke Pemuda / Warga MTA
        if (empty($gdm->alamat) && ($gdm->pemuda_id || !empty($gdm->mta_warga_uuid))) {
            $gdm->syncAlamatFromSource($this->apiService);
        }

        return redirect()->route('admin.gdm.index')
            ->with('success', "Data GDM {$gdm->nama} berhasil diperbarui.");
    }

    /**
     * Hapus data Guru Daerah Muda
     */
    public function delete(int $id)
    {
        $gdm = GuruDaerahMuda::findOrFail($id);
        $nama = $gdm->nama;
        $gdm->delete();

        return redirect()->route('admin.gdm.index')
            ->with('success', "Guru Daerah Muda {$nama} berhasil dihapus.");
    }

    /**
     * Tambah Riwayat Penugasan Kajian untuk seorang GDM
     */
    public function tambahPenugasan(Request $request, int $id)
    {
        $gdm = GuruDaerahMuda::findOrFail($id);

        $request->validate([
            'tahun'       => 'required|integer|min:2000|max:2100',
            'cabang_id'   => 'required|integer|exists:cabang,id',
            'hari_kajian' => 'nullable|max:50',
            'jam_kajian'  => 'nullable|max:50',
            'status'      => 'required|in:aktif,selesai,ditarik',
            'keterangan'  => 'nullable',
        ]);

        $cabang = Cabang::find((int) $request->input('cabang_id'));
        $hariKajian = $request->input('hari_kajian') ?: ($cabang?->gelombang_hari ?: null);
        $jamKajian  = $request->input('jam_kajian') ?: ($cabang?->gelombang_jam ?: null);

        $penugasan = GdmPenugasan::create([
            'gdm_id'      => $gdm->id,
            'tahun'       => (int) $request->input('tahun'),
            'cabang_id'   => (int) $request->input('cabang_id'),
            'hari_kajian' => $hariKajian,
            'jam_kajian'  => $jamKajian,
            'status'      => $request->input('status', 'aktif'),
            'keterangan'  => $request->input('keterangan'),
        ]);

        // Sinkronkan ke master cabang
        app(GdmCabangSyncService::class)->syncPenugasanToCabang($penugasan);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Penugasan kajian cabang berhasil ditambahkan dan disinkronkan dengan Master Cabang.',
                'data'    => $penugasan->load('cabang.wilayah'),
            ]);
        }

        return redirect()->back()
            ->with('success', "Penugasan kajian cabang untuk {$gdm->nama} tahun {$penugasan->tahun} berhasil ditambahkan dan disinkronkan ke Master Cabang.");
    }

    /**
     * Perbarui Riwayat Penugasan Kajian
     */
    public function updatePenugasan(Request $request, int $penugasanId)
    {
        $penugasan = GdmPenugasan::findOrFail($penugasanId);
        $oldCabangId = $penugasan->cabang_id;

        $request->validate([
            'tahun'       => 'required|integer|min:2000|max:2100',
            'cabang_id'   => 'required|integer|exists:cabang,id',
            'hari_kajian' => 'nullable|max:50',
            'jam_kajian'  => 'nullable|max:50',
            'status'      => 'required|in:aktif,selesai,ditarik',
            'keterangan'  => 'nullable',
        ]);

        $penugasan->update([
            'tahun'       => (int) $request->input('tahun'),
            'cabang_id'   => (int) $request->input('cabang_id'),
            'hari_kajian' => $request->input('hari_kajian'),
            'jam_kajian'  => $request->input('jam_kajian'),
            'status'      => $request->input('status'),
            'keterangan'  => $request->input('keterangan'),
        ]);

        $syncService = app(GdmCabangSyncService::class);
        $syncService->syncPenugasanToCabang($penugasan);

        if ($oldCabangId !== $penugasan->cabang_id) {
            $oldCabang = Cabang::find($oldCabangId);
            if ($oldCabang) {
                $syncService->refreshCabangUstadz($oldCabang);
            }
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Penugasan kajian berhasil diperbarui dan disinkronkan.',
                'data'    => $penugasan->load('cabang.wilayah'),
            ]);
        }

        return redirect()->back()
            ->with('success', 'Riwayat penugasan kajian berhasil diperbarui.');
    }

    /**
     * Hapus Riwayat Penugasan Kajian
     */
    public function deletePenugasan(int $penugasanId)
    {
        $penugasan = GdmPenugasan::findOrFail($penugasanId);
        $cabang = $penugasan->cabang;
        $penugasan->delete();

        if ($cabang) {
            app(GdmCabangSyncService::class)->refreshCabangUstadz($cabang);
        }

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Riwayat penugasan berhasil dihapus dan jadwal ustadz cabang diperbarui.',
            ]);
        }

        return redirect()->back()->with('success', 'Riwayat penugasan kajian berhasil dihapus.');
    }

    /**
     * Sinkronkan data penugasan GDM dengan seluruh data Master Cabang
     */
    public function syncCabang(Request $request, GdmCabangSyncService $syncService)
    {
        $result = $syncService->syncAll();

        if ($result['status'] === 'error') {
            return redirect()->back()->with('error', $result['message']);
        }

        return redirect()->route('admin.gdm.index')->with('success', $result['message']);
    }

    /**
     * Sinkronkan seluruh alamat domisili GDM dari data Pemuda / Warga MTA
     */
    public function syncAlamat(Request $request, GdmCabangSyncService $syncService)
    {
        $result = $syncService->syncAllAlamat();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($result);
        }

        if ($result['status'] === 'success') {
            return redirect()->back()->with('success', $result['message']);
        }

        return redirect()->back()->with('error', $result['message'] ?? 'Gagal menyinkronkan alamat domisili GDM.');
    }

    /**
     * Sinkronkan alamat domisili satu kader GDM dari data Pemuda atau Warga MTA
     */
    public function syncAlamatSingle(Request $request, int $id)
    {
        $gdm = GuruDaerahMuda::findOrFail($id);
        $changed = $gdm->syncAlamatFromSource($this->apiService);

        $msg = $changed
            ? "Alamat domisili GDM {$gdm->nama} berhasil disinkronkan: {$gdm->alamat}"
            : "Alamat domisili GDM {$gdm->nama} sudah mutakhir sesuai data sumber ({$gdm->sumber_alamat_label}).";

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => $msg,
                'data'    => [
                    'id'                  => $gdm->id,
                    'alamat'              => $gdm->alamat,
                    'sumber_alamat_label' => $gdm->sumber_alamat_label,
                ],
            ]);
        }

        return redirect()->back()->with('success', $msg);
    }

    /**
     * Pencarian Gabungan (Seamless) Profil Pemuda Sragen & Warga MTA Pusat untuk GDM
     */
    public function searchUnified(Request $request): JsonResponse
    {
        $q = trim((string) $request->input('q'));

        if (mb_strlen($q) < 2) {
            return response()->json([
                'status' => 'success',
                'data'   => [],
            ]);
        }

        $existingGdmPemudaIds = GuruDaerahMuda::whereNotNull('pemuda_id')->pluck('pemuda_id')->all();
        $existingGdmWargaUuids = GuruDaerahMuda::whereNotNull('mta_warga_uuid')->pluck('mta_warga_uuid')->all();

        // 1. Cari Data Pemuda Lokal Sragen
        $pemudaQuery = Pemuda::with(['cabang.wilayah', 'alamat.village', 'alamat.district', 'alamat.regency'])
            ->where('status_data', '!=', 'archived');

        $s = '%' . $q . '%';
        $pemudaQuery->where(function ($query) use ($s) {
            $query->where('name', 'LIKE', $s)
                  ->orWhere('phone', 'LIKE', $s)
                  ->orWhere('registration_number', 'LIKE', $s);
        });

        $pemudaList = $pemudaQuery->limit(20)->get();

        $seenMtaUuids = [];
        $results = [];

        foreach ($pemudaList as $p) {
            $isGdm = in_array($p->id, $existingGdmPemudaIds, true)
                || (!empty($p->mta_warga_uuid) && in_array($p->mta_warga_uuid, $existingGdmWargaUuids, true));

            if (!empty($p->mta_warga_uuid)) {
                $seenMtaUuids[] = $p->mta_warga_uuid;
            }

            $formattedBirthDate = $p->birth_date ? Carbon::parse($p->birth_date)->format('Y-m-d') : null;
            $readableBirthDate  = $p->birth_date ? Carbon::parse($p->birth_date)->translatedFormat('d M Y') : null;
            $alamat = $p->alamat?->alamat_lengkap ?: '';

            $results[] = [
                'sumber_data'             => 'pemuda',
                'sumber_label'            => 'Pemuda MTA Sragen',
                'id'                      => $p->id,
                'uuid'                    => $p->mta_warga_uuid,
                'nama'                    => $p->name,
                'gender'                  => $p->gender,
                'gender_label'            => $p->gender === 'L' ? 'Ikhwan' : 'Akhwat',
                'tempat_lahir'            => $p->birth_place,
                'tanggal_lahir'           => $formattedBirthDate,
                'tanggal_lahir_formatted' => $readableBirthDate,
                'cabang_id'               => $p->cabang_id,
                'cabang_name'             => $p->cabang?->name ?? '-',
                'wilayah_name'            => $p->cabang?->wilayah?->name ?? '-',
                'alamat'                  => $alamat,
                'no_wa'                   => $p->phone,
                'reg_no'                  => $p->registration_number,
                'is_gdm'                  => $isGdm,
            ];
        }

        // 2. Cari Data Warga MTA Pusat (Jika query minimal 3 karakter)
        if (mb_strlen($q) >= 3) {
            try {
                $res = $this->apiService->searchWarga($q, ['limit' => 15]);

                if (($res['success'] ?? false) && !empty($res['data'])) {
                    $cabangs = Cabang::with('wilayah')->get(['id', 'name', 'wilayah_id', 'mta_uuid']);

                    foreach ($res['data'] as $w) {
                        $uuid = $w['uuid'] ?? ($w['id'] ?? null);

                        if ($uuid && in_array($uuid, $seenMtaUuids, true)) {
                            continue;
                        }

                        $isGdm = $uuid ? in_array($uuid, $existingGdmWargaUuids, true) : false;

                        $cabangName = $w['cabang'] ?? ($w['cabang_nama'] ?? null);
                        $cabangUuid = $w['cabang_uuid'] ?? null;
                        $matchedCabangId = null;
                        $matchedWilayahName = null;

                        if ($cabangUuid) {
                            $c = $cabangs->firstWhere('mta_uuid', $cabangUuid);
                            if ($c) {
                                $matchedCabangId    = $c->id;
                                $cabangName         = $c->name;
                                $matchedWilayahName = $c->wilayah?->name;
                            }
                        }

                        if (!$matchedCabangId && $cabangName) {
                            $c = $cabangs->first(fn($item) => strtolower($item->name) === strtolower($cabangName));
                            if ($c) {
                                $matchedCabangId    = $c->id;
                                $matchedWilayahName = $c->wilayah?->name;
                            }
                        }

                        $tglLahir = null;
                        $tglLahirFormatted = null;
                        $tglLahirRaw = $w['tanggal_lahir'] ?? ($w['tgl_lahir'] ?? null);
                        if ($tglLahirRaw) {
                            try {
                                $parsed = Carbon::parse($tglLahirRaw);
                                $tglLahir = $parsed->format('Y-m-d');
                                $tglLahirFormatted = $parsed->translatedFormat('d M Y');
                            } catch (\Throwable) {
                                $tglLahir = null;
                                $tglLahirFormatted = null;
                            }
                        }

                        $results[] = [
                            'sumber_data'             => 'warga',
                            'sumber_label'            => 'Warga MTA Pusat',
                            'id'                      => null,
                            'uuid'                    => $uuid,
                            'nama'                    => $w['nama'] ?? ($w['name'] ?? '-'),
                            'gender'                  => null,
                            'gender_label'            => null,
                            'tempat_lahir'            => $w['tempat_lahir'] ?? null,
                            'tanggal_lahir'           => $tglLahir,
                            'tanggal_lahir_formatted' => $tglLahirFormatted,
                            'cabang_id'               => $matchedCabangId,
                            'cabang_name'             => $cabangName ?? '-',
                            'wilayah_name'            => $matchedWilayahName ?? '-',
                            'alamat'                  => $w['alamat'] ?? ($w['alamat_lengkap'] ?? null),
                            'no_wa'                   => $w['telepon'] ?? ($w['hp'] ?? ($w['no_wa'] ?? null)),
                            'reg_no'                  => null,
                            'status_warga'            => $w['status'] ?? ($w['status_warga'] ?? 'Warga MTA'),
                            'is_gdm'                  => $isGdm,
                        ];
                    }
                }
            } catch (\Throwable) {
                // Fail-safe
            }
        }

        return response()->json([
            'status' => 'success',
            'data'   => $results,
        ]);
    }

    /**
     * Pencarian Data Pemuda untuk Diambil Menjadi GDM
     */
    public function searchPemuda(Request $request): JsonResponse
    {
        $q = trim((string) $request->input('q'));

        if (mb_strlen($q) < 2) {
            return response()->json([
                'status' => 'success',
                'data'   => [],
            ]);
        }

        $query = Pemuda::with(['cabang', 'alamat.village', 'alamat.district', 'alamat.regency'])
            ->where('status_data', '!=', 'archived');

        $s = '%' . $q . '%';
        $query->where(function ($query) use ($s) {
            $query->where('name', 'LIKE', $s)
                  ->orWhere('phone', 'LIKE', $s)
                  ->orWhere('registration_number', 'LIKE', $s);
        });

        $pemuda = $query->limit(20)->get();

        $results = $pemuda->map(function ($p) {
            $formattedBirthDate = $p->birth_date ? Carbon::parse($p->birth_date)->format('Y-m-d') : null;
            $readableBirthDate  = $p->birth_date ? Carbon::parse($p->birth_date)->translatedFormat('d M Y') : null;

            // Alamat lengkap
            $alamat = $p->alamat?->alamat_lengkap ?: '';

            return [
                'id'            => $p->id,
                'nama'          => $p->name,
                'gender'        => $p->gender,
                'gender_label'  => $p->gender === 'L' ? 'Ikhwan' : 'Akhwat',
                'tempat_lahir'  => $p->birth_place,
                'tanggal_lahir' => $formattedBirthDate,
                'tanggal_lahir_formatted' => $readableBirthDate,
                'cabang_id'     => $p->cabang_id,
                'cabang_name'   => $p->cabang?->name ?? '-',
                'alamat'        => $alamat,
                'no_wa'         => $p->phone,
                'reg_no'        => $p->registration_number,
                'mta_uuid'      => $p->mta_warga_uuid,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data'   => $results,
        ]);
    }

    /**
     * Pencarian Data Warga MTA (api.mta.or.id) untuk Diambil Menjadi GDM
     */
    public function searchWarga(Request $request): JsonResponse
    {
        $q = trim((string) $request->input('q'));

        if (mb_strlen($q) < 3) {
            return response()->json([
                'status' => 'success',
                'data'   => [],
            ]);
        }

        $res = $this->apiService->searchWarga($q, ['limit' => 20]);

        if (!($res['success'] ?? false) || empty($res['data'])) {
            return response()->json([
                'status'  => 'success',
                'data'    => [],
                'message' => $res['message'] ?? 'Tidak ditemukan data warga yang cocok.',
            ]);
        }

        $cabangs = Cabang::all(['id', 'name', 'mta_uuid']);

        $results = collect($res['data'])->map(function ($w) use ($cabangs) {
            $cabangName = $w['cabang'] ?? ($w['cabang_nama'] ?? null);
            $cabangUuid = $w['cabang_uuid'] ?? null;
            $matchedCabangId = null;

            if ($cabangUuid) {
                $c = $cabangs->firstWhere('mta_uuid', $cabangUuid);
                if ($c) {
                    $matchedCabangId = $c->id;
                    $cabangName      = $c->name;
                }
            }

            if (!$matchedCabangId && $cabangName) {
                $c = $cabangs->first(fn($item) => strtolower($item->name) === strtolower($cabangName));
                if ($c) {
                    $matchedCabangId = $c->id;
                }
            }

            $tglLahir = null;
            $tglLahirRaw = $w['tanggal_lahir'] ?? ($w['tgl_lahir'] ?? null);
            if ($tglLahirRaw) {
                try {
                    $tglLahir = Carbon::parse($tglLahirRaw)->format('Y-m-d');
                } catch (\Throwable) {
                    $tglLahir = null;
                }
            }

            return [
                'uuid'          => $w['uuid'] ?? ($w['id'] ?? null),
                'nama'          => $w['nama'] ?? ($w['name'] ?? '-'),
                'tempat_lahir'  => $w['tempat_lahir'] ?? null,
                'tanggal_lahir' => $tglLahir,
                'cabang_id'     => $matchedCabangId,
                'cabang_name'   => $cabangName ?? '-',
                'alamat'        => $w['alamat'] ?? ($w['alamat_lengkap'] ?? null),
                'no_wa'         => $w['telepon'] ?? ($w['hp'] ?? ($w['no_wa'] ?? null)),
                'status_warga'  => $w['status'] ?? ($w['status_warga'] ?? 'Warga MTA'),
            ];
        });

        return response()->json([
            'status' => 'success',
            'data'   => $results,
        ]);
    }
}
