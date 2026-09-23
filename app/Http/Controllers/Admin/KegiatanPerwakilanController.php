<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiSetting;
use App\Models\KegiatanPerwakilan;
use Carbon\Carbon;
use Illuminate\Http\Request;

class KegiatanPerwakilanController extends Controller
{
    /**
     * Tampilkan halaman utama pengelolaan Kegiatan Pemuda Perwakilan untuk Mobile Presensi
     */
    public function index(Request $request)
    {
        $query = KegiatanPerwakilan::query();

        // Filter Kategori
        if ($request->filled('kategori') && $request->kategori !== 'semua') {
            $query->where('kategori', $request->kategori);
        }

        // Filter Status
        if ($request->filled('status') && $request->status !== 'semua') {
            $query->where('status', $request->status);
        }

        // Filter Aktif / Non-Aktif
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active === '1');
        }

        // Pencarian
        if ($request->filled('q')) {
            $keyword = trim($request->q);
            $query->where(function ($q) use ($keyword) {
                $q->where('nama_kegiatan', 'LIKE', "%{$keyword}%")
                  ->orWhere('lokasi', 'LIKE', "%{$keyword}%")
                  ->orWhere('pemateri', 'LIKE', "%{$keyword}%")
                  ->orWhere('deskripsi', 'LIKE', "%{$keyword}%");
            });
        }

        $kegiatanList = $query->orderBy('tanggal', 'ASC')->paginate(15)->withQueryString();

        // Statistik Dashboard
        $totalKegiatan  = KegiatanPerwakilan::count();
        $totalAktif     = KegiatanPerwakilan::where('is_active', true)->count();
        $totalMendatang = KegiatanPerwakilan::where('status', 'Akan Datang')->count();
        $totalSelesai   = KegiatanPerwakilan::where('status', 'Selesai')->count();

        // Pesan Siaran Saat Ini
        $broadcastMessage = ApiSetting::get('api_broadcast_message', '');

        $kategoriOptions = [
            'Kajian Akbar',
            'Bakti Dakwah',
            'Diklat & Pelatihan',
            'Olahraga',
            'Musyawarah',
        ];

        return view('admin.kegiatan_perwakilan.index', [
            'title'            => 'Kelola Info Kegiatan Pemuda (Mobile Presensi)',
            'kegiatanList'     => $kegiatanList,
            'totalKegiatan'    => $totalKegiatan,
            'totalAktif'       => $totalAktif,
            'totalMendatang'   => $totalMendatang,
            'totalSelesai'     => $totalSelesai,
            'broadcastMessage' => $broadcastMessage,
            'kategoriOptions'  => $kategoriOptions,
        ]);
    }

    /**
     * Simpan Kegiatan Baru
     */
    public function simpan(Request $request)
    {
        $request->validate([
            'nama_kegiatan'     => 'required|string|max:200',
            'kategori'          => 'required|string|max:50',
            'tanggal'           => 'required|date',
            'hari_tanggal'      => 'nullable|string|max:100',
            'jam'               => 'required|string|max:100',
            'lokasi'            => 'required|string|max:255',
            'alamat_detail'     => 'nullable|string',
            'pemateri'          => 'nullable|string|max:200',
            'target_peserta'    => 'required|string|max:200',
            'penyelenggara'     => 'required|string|max:200',
            'deskripsi'         => 'required|string',
            'catatan_ketentuan' => 'nullable|string',
            'narahubung'        => 'nullable|string|max:150',
            'status'            => 'required|in:Akan Datang,Segera,Berlangsung,Selesai',
            'is_active'         => 'nullable|boolean',
        ], [
            'nama_kegiatan.required' => 'Nama kegiatan wajib diisi.',
            'kategori.required'      => 'Kategori kegiatan wajib dipilih.',
            'tanggal.required'       => 'Tanggal pelaksanaan wajib diisi.',
            'jam.required'           => 'Waktu / jam kegiatan wajib diisi.',
            'lokasi.required'        => 'Lokasi kegiatan wajib diisi.',
            'deskripsi.required'     => 'Deskripsi ringkas kegiatan wajib diisi.',
        ]);

        $hariTanggal = trim((string)$request->input('hari_tanggal'));
        if (empty($hariTanggal)) {
            $date = Carbon::parse($request->input('tanggal'));
            $hariTanggal = $date->locale('id')->isoFormat('dddd, D MMMM Y');
        }

        KegiatanPerwakilan::create([
            'nama_kegiatan'     => trim($request->input('nama_kegiatan')),
            'kategori'          => trim($request->input('kategori')),
            'tanggal'           => $request->input('tanggal'),
            'hari_tanggal'      => $hariTanggal,
            'jam'               => trim($request->input('jam')),
            'lokasi'            => trim($request->input('lokasi')),
            'alamat_detail'     => trim((string)$request->input('alamat_detail')) ?: null,
            'pemateri'          => trim((string)$request->input('pemateri')) ?: null,
            'target_peserta'    => trim($request->input('target_peserta')),
            'penyelenggara'     => trim($request->input('penyelenggara')),
            'deskripsi'         => trim($request->input('deskripsi')),
            'catatan_ketentuan' => trim((string)$request->input('catatan_ketentuan')) ?: null,
            'narahubung'        => trim((string)$request->input('narahubung')) ?: null,
            'status'            => $request->input('status', 'Akan Datang'),
            'is_active'         => $request->has('is_active') ? (bool)$request->input('is_active') : true,
            'created_by'        => auth()->id(),
        ]);

        return redirect()->route('admin.kegiatan-perwakilan.index')
            ->with('success', 'Agenda kegiatan berhasil ditambahkan dan otomatis tampil di aplikasi mobile Presensi PMD.');
    }

    /**
     * Update Kegiatan
     */
    public function update(Request $request, $id)
    {
        $kegiatan = KegiatanPerwakilan::findOrFail($id);

        $request->validate([
            'nama_kegiatan'     => 'required|string|max:200',
            'kategori'          => 'required|string|max:50',
            'tanggal'           => 'required|date',
            'hari_tanggal'      => 'nullable|string|max:100',
            'jam'               => 'required|string|max:100',
            'lokasi'            => 'required|string|max:255',
            'alamat_detail'     => 'nullable|string',
            'pemateri'          => 'nullable|string|max:200',
            'target_peserta'    => 'required|string|max:200',
            'penyelenggara'     => 'required|string|max:200',
            'deskripsi'         => 'required|string',
            'catatan_ketentuan' => 'nullable|string',
            'narahubung'        => 'nullable|string|max:150',
            'status'            => 'required|in:Akan Datang,Segera,Berlangsung,Selesai',
            'is_active'         => 'nullable|boolean',
        ]);

        $hariTanggal = trim((string)$request->input('hari_tanggal'));
        if (empty($hariTanggal)) {
            $date = Carbon::parse($request->input('tanggal'));
            $hariTanggal = $date->locale('id')->isoFormat('dddd, D MMMM Y');
        }

        $kegiatan->update([
            'nama_kegiatan'     => trim($request->input('nama_kegiatan')),
            'kategori'          => trim($request->input('kategori')),
            'tanggal'           => $request->input('tanggal'),
            'hari_tanggal'      => $hariTanggal,
            'jam'               => trim($request->input('jam')),
            'lokasi'            => trim($request->input('lokasi')),
            'alamat_detail'     => trim((string)$request->input('alamat_detail')) ?: null,
            'pemateri'          => trim((string)$request->input('pemateri')) ?: null,
            'target_peserta'    => trim($request->input('target_peserta')),
            'penyelenggara'     => trim($request->input('penyelenggara')),
            'deskripsi'         => trim($request->input('deskripsi')),
            'catatan_ketentuan' => trim((string)$request->input('catatan_ketentuan')) ?: null,
            'narahubung'        => trim((string)$request->input('narahubung')) ?: null,
            'status'            => $request->input('status'),
            'is_active'         => $request->has('is_active') ? (bool)$request->input('is_active') : true,
        ]);

        return redirect()->route('admin.kegiatan-perwakilan.index')
            ->with('success', "Informasi kegiatan '{$kegiatan->nama_kegiatan}' berhasil diperbarui.");
    }

    /**
     * Toggle status aktif / tampil di mobile
     */
    public function toggleStatus($id)
    {
        $kegiatan = KegiatanPerwakilan::findOrFail($id);
        $kegiatan->update(['is_active' => !$kegiatan->is_active]);

        $statusText = $kegiatan->is_active ? 'diaktifkan (tampil di aplikasi)' : 'dinonaktifkan (disembunyikan dari aplikasi)';
        return redirect()->route('admin.kegiatan-perwakilan.index')
            ->with('success', "Kegiatan '{$kegiatan->nama_kegiatan}' berhasil {$statusText}.");
    }

    /**
     * Hapus Kegiatan
     */
    public function delete($id)
    {
        $kegiatan = KegiatanPerwakilan::findOrFail($id);
        $nama = $kegiatan->nama_kegiatan;
        $kegiatan->delete();

        return redirect()->route('admin.kegiatan-perwakilan.index')
            ->with('success', "Agenda kegiatan '{$nama}' berhasil dihapus.");
    }

    /**
     * Perbarui Pesan Siaran / Maklumat Perwakilan
     */
    public function updateBroadcast(Request $request)
    {
        $request->validate([
            'api_broadcast_message' => 'nullable|string|max:1000',
        ]);

        ApiSetting::set('api_broadcast_message', trim((string)$request->input('api_broadcast_message')));

        return redirect()->route('admin.kegiatan-perwakilan.index')
            ->with('success', 'Pesan siaran maklumat perwakilan berhasil diperbarui dan disiarkan ke aplikasi mobile.');
    }

    /**
     * Hapus Semua Agenda Kegiatan Sekaligus
     */
    public function hapusSemua()
    {
        $count = KegiatanPerwakilan::count();
        KegiatanPerwakilan::query()->delete();

        return redirect()->route('admin.kegiatan-perwakilan.index')
            ->with('success', "Seluruh ({$count}) agenda kegiatan perwakilan berhasil dihapus secara permanen.");
    }

    /**
     * Reset / Muat Ulang Template Agenda Kegiatan Bawaan
     */
    public function resetDefaults()
    {
        KegiatanPerwakilan::query()->delete();
        KegiatanPerwakilan::seedDefaults(true);

        return redirect()->route('admin.kegiatan-perwakilan.index')
            ->with('success', 'Template agenda kegiatan resmi perwakilan berhasil dimuat ulang.');
    }
}
