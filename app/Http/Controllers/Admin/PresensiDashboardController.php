<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiSetting;
use App\Models\Cabang;
use App\Models\KegiatanPerwakilan;
use App\Models\KegiatanPresensi;
use App\Models\PresensiDetail;
use App\Models\Wilayah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PresensiDashboardController extends Controller
{
    /**
     * Tampilkan Dashboard Management API & Mobile Presensi PMD
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $role = session('role') ?? $user?->role?->name;
        $wilayahId = session('wilayah_id') ?? $user?->wilayah_id;
        $cabangId = session('cabang_id') ?? $user?->cabang_id;

        // Ambil pengaturan API Mobile
        $settings = ApiSetting::getAllSettings();
        $apiStatus = ($settings['api_presensi_enabled'] ?? '1') === '1';

        // Query dasar Kegiatan Presensi dengan Scoping RBAC
        $kegiatanQuery = KegiatanPresensi::with(['cabang.wilayah', 'creator:id,name'])
            ->withCount([
                'presensiDetails as hadir_count' => fn($q) => $q->where('status_kehadiran', 'hadir'),
                'presensiDetails as total_recorded_count',
            ]);

        if (in_array($role, ['admin_cabang'], true) && !empty($cabangId)) {
            $kegiatanQuery->where('cabang_id', $cabangId);
        } elseif (in_array($role, ['admin_wilayah', 'admin_wilayah_pemuda'], true) && !empty($wilayahId)) {
            $kegiatanQuery->whereHas('cabang', function ($q) use ($wilayahId) {
                $q->where('wilayah_id', $wilayahId);
            });
        }

        // Filter dari Request (Wilayah, Cabang, Status, Tanggal)
        $filterWilayah = $request->input('wilayah_id');
        $filterCabang  = $request->input('cabang_id');
        $filterStatus  = $request->input('status');
        $filterSearch  = $request->input('q');

        if ($filterWilayah && in_array($role, ['superadmin', 'koordinator_gdm'], true)) {
            $kegiatanQuery->whereHas('cabang', function ($q) use ($filterWilayah) {
                $q->where('wilayah_id', $filterWilayah);
            });
        }

        if ($filterCabang && in_array($role, ['superadmin', 'koordinator_gdm', 'admin_pemuda', 'admin_pemudi', 'admin_wilayah'], true)) {
            $kegiatanQuery->where('cabang_id', $filterCabang);
        }

        if ($filterStatus && in_array($filterStatus, ['draft', 'berlangsung', 'selesai'], true)) {
            $kegiatanQuery->where('status', $filterStatus);
        }

        if (!empty($filterSearch)) {
            $kegiatanQuery->where(function ($q) use ($filterSearch) {
                $q->where('nama_kegiatan', 'like', "%{$filterSearch}%")
                  ->orWhere('pemateri', 'like', "%{$filterSearch}%")
                  ->orWhere('lokasi', 'like', "%{$filterSearch}%")
                  ->orWhereHas('cabang', function ($qc) use ($filterSearch) {
                      $qc->where('name', 'like', "%{$filterSearch}%");
                  });
            });
        }

        // Salinan query untuk penghitungan agregat statistik
        $scopedKegiatanIds = (clone $kegiatanQuery)->pluck('id');

        // Statistik Utama
        $totalKegiatan = $scopedKegiatanIds->count();
        $kegiatanSelesai = (clone $kegiatanQuery)->where('status', 'selesai')->count();
        $kegiatanBerlangsung = (clone $kegiatanQuery)->where('status', 'berlangsung')->count();
        $kegiatanDraft = (clone $kegiatanQuery)->where('status', 'draft')->count();

        // Statistik Kehadiran dari presensi_detail yang relevan
        $detailStats = PresensiDetail::whereIn('kegiatan_presensi_id', $scopedKegiatanIds)
            ->select('status_kehadiran', DB::raw('count(*) as count'))
            ->groupBy('status_kehadiran')
            ->pluck('count', 'status_kehadiran')
            ->toArray();

        $kehadiranHadir = (int) ($detailStats['hadir'] ?? 0);
        $kehadiranIzin  = (int) ($detailStats['izin'] ?? 0);
        $kehadiranSakit = (int) ($detailStats['sakit'] ?? 0);
        $kehadiranAlpa  = (int) ($detailStats['alpa'] ?? 0);
        $totalPresensiRecorded = $kehadiranHadir + $kehadiranIzin + $kehadiranSakit + $kehadiranAlpa;

        // Persentase Kehadiran
        $persentaseHadir = $totalPresensiRecorded > 0 ? round(($kehadiranHadir / $totalPresensiRecorded) * 100, 1) : 0;

        // Personal Access Tokens (Sanctum) monitoring
        $tokensQuery = DB::table('personal_access_tokens')
            ->join('users', 'personal_access_tokens.tokenable_id', '=', 'users.id')
            ->leftJoin('cabang', 'users.cabang_id', '=', 'cabang.id')
            ->select([
                'personal_access_tokens.id',
                'personal_access_tokens.name as device_name',
                'personal_access_tokens.last_used_at',
                'personal_access_tokens.created_at',
                'users.name as user_name',
                'users.username',
                'cabang.name as cabang_name',
            ]);

        if (in_array($role, ['admin_cabang'], true) && !empty($cabangId)) {
            $tokensQuery->where('users.cabang_id', $cabangId);
        } elseif (in_array($role, ['admin_wilayah', 'admin_wilayah_pemuda'], true) && !empty($wilayahId)) {
            $tokensQuery->where('users.wilayah_id', $wilayahId);
        }

        $totalTokens = (clone $tokensQuery)->count();
        $activeTokens7Days = (clone $tokensQuery)->where('personal_access_tokens.last_used_at', '>=', now()->subDays(7))->count();
        $recentTokens = (clone $tokensQuery)->orderBy('personal_access_tokens.created_at', 'DESC')->limit(8)->get();

        // Info Kegiatan Perwakilan
        $totalKegiatanPerwakilan = KegiatanPerwakilan::where('is_active', true)->count();

        // Jumlah Cabang Aktif Menggunakan Presensi
        $cabangAktifCount = KegiatanPresensi::distinct('cabang_id')->count('cabang_id');

        // Daftar Kegiatan Terbaru (15 data dengan pagination sederhana)
        $recentKegiatan = $kegiatanQuery->orderBy('tanggal', 'DESC')
            ->orderBy('created_at', 'DESC')
            ->paginate(15)
            ->withQueryString();

        // Statistik per Wilayah untuk Grafik
        $wilayahStats = Wilayah::orderBy('id', 'ASC')->get()->map(function ($w) {
            $cabangIds = Cabang::where('wilayah_id', $w->id)->pluck('id');
            $w->total_kegiatan = KegiatanPresensi::whereIn('cabang_id', $cabangIds)->count();
            return $w;
        });

        // Daftar Referensi Dropdown
        $wilayahList = Wilayah::orderBy('id', 'ASC')->get();
        $cabangQuery = Cabang::orderBy('name', 'ASC');
        if (!empty($filterWilayah)) {
            $cabangQuery->where('wilayah_id', $filterWilayah);
        } elseif (in_array($role, ['admin_wilayah', 'admin_wilayah_pemuda'], true) && !empty($wilayahId)) {
            $cabangQuery->where('wilayah_id', $wilayahId);
        }
        $cabangList = $cabangQuery->get();

        return view('admin.presensi_dashboard.index', [
            'title'                   => 'Dashboard API & Presensi PMD Mobile',
            'settings'                => $settings,
            'apiStatus'               => $apiStatus,
            'totalKegiatan'           => $totalKegiatan,
            'kegiatanSelesai'         => $kegiatanSelesai,
            'kegiatanBerlangsung'     => $kegiatanBerlangsung,
            'kegiatanDraft'           => $kegiatanDraft,
            'kehadiranHadir'          => $kehadiranHadir,
            'kehadiranIzin'           => $kehadiranIzin,
            'kehadiranSakit'          => $kehadiranSakit,
            'kehadiranAlpa'           => $kehadiranAlpa,
            'totalPresensiRecorded'   => $totalPresensiRecorded,
            'persentaseHadir'         => $persentaseHadir,
            'totalTokens'             => $totalTokens,
            'activeTokens7Days'       => $activeTokens7Days,
            'recentTokens'            => $recentTokens,
            'totalKegiatanPerwakilan' => $totalKegiatanPerwakilan,
            'cabangAktifCount'        => $cabangAktifCount,
            'recentKegiatan'          => $recentKegiatan,
            'wilayahStats'            => $wilayahStats,
            'wilayahList'             => $wilayahList,
            'cabangList'              => $cabangList,
            'userRole'                => $role,
            'filters'                 => [
                'wilayah_id' => $filterWilayah,
                'cabang_id'  => $filterCabang,
                'status'     => $filterStatus,
                'q'          => $filterSearch,
            ],
        ]);
    }

    /**
     * Ambil rincian rekap presensi suatu sesi kegiatan via JSON untuk modal detail
     */
    public function rekapDetail($id)
    {
        $user = auth()->user();
        $role = session('role') ?? $user?->role?->name;
        $wilayahId = session('wilayah_id') ?? $user?->wilayah_id;
        $cabangId = session('cabang_id') ?? $user?->cabang_id;

        $kegiatan = KegiatanPresensi::with(['cabang.wilayah', 'creator:id,name'])
            ->findOrFail($id);

        // Validasi Scope Akses
        if (in_array($role, ['admin_cabang'], true) && $kegiatan->cabang_id != $cabangId) {
            return response()->json(['error' => 'Akses ditolak ke cabang lain.'], 403);
        }
        if (in_array($role, ['admin_wilayah', 'admin_wilayah_pemuda'], true) && $kegiatan->cabang?->wilayah_id != $wilayahId) {
            return response()->json(['error' => 'Akses ditolak ke wilayah lain.'], 403);
        }

        $rekap = $kegiatan->getRekapSummary();
        $waText = $kegiatan->generateWhatsAppText();

        return response()->json([
            'id'             => $kegiatan->id,
            'nama_kegiatan'  => $kegiatan->nama_kegiatan,
            'cabang_name'    => $kegiatan->cabang?->name ?? '-',
            'wilayah_name'   => $kegiatan->cabang?->wilayah?->name ?? '-',
            'tanggal'        => $kegiatan->tanggal ? $kegiatan->tanggal->translatedFormat('d F Y') : '-',
            'hari_tanggal'   => $kegiatan->tanggal ? $kegiatan->tanggal->locale('id')->translatedFormat('l, d F Y') : '-',
            'jam_mulai'      => $kegiatan->jam_mulai ? substr($kegiatan->jam_mulai, 0, 5) : null,
            'jam_selesai'    => $kegiatan->jam_selesai ? substr($kegiatan->jam_selesai, 0, 5) : null,
            'lokasi'         => $kegiatan->lokasi ?: '-',
            'pemateri'       => $kegiatan->pemateri ?: '-',
            'status'         => $kegiatan->status,
            'catatan'        => $kegiatan->catatan,
            'notulensi'      => $kegiatan->notulensi,
            'notulis'        => $kegiatan->notulis,
            'has_notulensi'  => $kegiatan->hasNotulensi(),
            'creator_name'   => $kegiatan->creator?->name ?? '-',
            'rekap'          => $rekap,
            'whatsapp_text'  => $waText,
        ]);
    }

    /**
     * Simpan / Perbarui notulensi kegiatan kajian cabang
     */
    public function updateNotulensi(Request $request, $id)
    {
        $user = auth()->user();
        $role = session('role') ?? $user?->role?->name;
        $wilayahId = session('wilayah_id') ?? $user?->wilayah_id;
        $cabangId = session('cabang_id') ?? $user?->cabang_id;

        $kegiatan = KegiatanPresensi::with(['cabang.wilayah'])->findOrFail($id);

        // Validasi Scope Akses
        if ($role === 'koordinator_gdm') {
            return response()->json(['success' => false, 'message' => 'Role Koordinator GDM hanya memiliki hak baca (view-only) dan tidak diizinkan mengubah notulensi kajian.'], 403);
        }
        if (in_array($role, ['admin_cabang'], true) && $kegiatan->cabang_id != $cabangId) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak ke cabang lain.'], 403);
        }
        if (in_array($role, ['admin_wilayah', 'admin_wilayah_pemuda'], true) && $kegiatan->cabang?->wilayah_id != $wilayahId) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak ke wilayah lain.'], 403);
        }

        $request->validate([
            'notulensi' => 'nullable|string',
            'notulis'   => 'nullable|string|max:150',
        ]);

        $kegiatan->update([
            'notulensi' => $request->input('notulensi'),
            'notulis'   => $request->input('notulis'),
        ]);

        return response()->json([
            'success'       => true,
            'message'       => 'Notulensi kajian cabang berhasil diperbarui.',
            'notulensi'     => $kegiatan->notulensi,
            'notulis'       => $kegiatan->notulis,
            'has_notulensi' => $kegiatan->hasNotulensi(),
            'whatsapp_text' => $kegiatan->generateWhatsAppText(),
        ]);
    }
}
