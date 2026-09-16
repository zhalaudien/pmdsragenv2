<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cabang;
use App\Models\Wilayah;
use App\Models\Pemuda;
use App\Models\User;
use Illuminate\Http\Request;

class CabangController extends Controller
{
    public function index(Request $request)
    {
        $wilayahId    = $request->input('wilayah_id');
        $hasGelombang = $request->input('has_gelombang');
        $search       = $request->input('search');

        $query = Cabang::with('wilayah');

        if (!empty($wilayahId)) {
            $query->where('wilayah_id', (int) $wilayahId);
        }

        if (!empty($hasGelombang) && in_array($hasGelombang, ['sudah', 'belum'], true)) {
            $query->where('has_gelombang', $hasGelombang);
        }

        if (!empty($search)) {
            $s = '%' . trim($search) . '%';
            $query->where(function ($q) use ($s) {
                $q->where('name', 'LIKE', $s)
                  ->orWhere('code', 'LIKE', $s)
                  ->orWhere('pimpinan_nama', 'LIKE', $s)
                  ->orWhere('alamat', 'LIKE', $s)
                  ->orWhere('gelombang_ustadz', 'LIKE', $s);
            });
        }

        $cabangList = $query->orderBy('wilayah_id', 'ASC')
            ->orderBy('name', 'ASC')
            ->paginate(20)
            ->withQueryString();

        foreach ($cabangList as $c) {
            $c->total_pemuda = Pemuda::where('cabang_id', $c->id)->count();
        }

        $totalCabang         = Cabang::count();
        $totalSudahGelombang = Cabang::where('has_gelombang', 'sudah')->count();
        $totalBelumGelombang = Cabang::where('has_gelombang', 'belum')->count();

        return view('admin.cabang.index', [
            'title'               => 'Manajemen Cabang',
            'cabangList'          => $cabangList,
            'wilayahList'         => Wilayah::orderBy('id', 'ASC')->get(),
            'selectedW'           => $wilayahId,
            'selectedGelombang'   => $hasGelombang,
            'search'              => $search,
            'totalCabang'         => $totalCabang,
            'totalSudahGelombang' => $totalSudahGelombang,
            'totalBelumGelombang' => $totalBelumGelombang,
            'user'                => session()->all(),
        ]);
    }

    public function detail(int $id)
    {
        $cabang = Cabang::with('wilayah')->find($id);

        if (!$cabang) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Data cabang tidak ditemukan.',
            ], 404);
        }

        $cabang->total_pemuda = Pemuda::where('cabang_id', $id)->count();

        return response()->json([
            'status' => 'success',
            'data'   => $cabang,
        ]);
    }

    public function simpan(Request $request)
    {
        $request->validate([
            'wilayah_id'       => 'required|integer|min:1',
            'name'             => 'required|min:3|max:100',
            'code'             => 'nullable|max:50',
            'alamat'           => 'nullable',
            'maps_url'         => 'nullable|max:500',
            'pimpinan_nama'    => 'nullable|max:100',
            'no_wa'            => 'nullable|max:20',
            'has_gelombang'    => 'required|in:sudah,belum',
            'gelombang_hari'   => 'nullable|max:100',
            'gelombang_jam'    => 'nullable|max:50',
            'gelombang_ustadz' => 'nullable|max:150',
            'description'      => 'nullable',
        ]);

        $hasGelombang = $request->input('has_gelombang') === 'sudah' ? 'sudah' : 'belum';

        Cabang::create([
            'wilayah_id'       => (int) $request->input('wilayah_id'),
            'code'             => $request->input('code') ? strtoupper(trim((string) $request->input('code'))) : null,
            'name'             => trim((string) $request->input('name')),
            'description'      => $request->input('description'),
            'alamat'           => $request->input('alamat'),
            'maps_url'         => $request->input('maps_url'),
            'pimpinan_nama'    => $request->input('pimpinan_nama'),
            'no_wa'            => $request->input('no_wa'),
            'has_gelombang'    => $hasGelombang,
            'gelombang_hari'   => $hasGelombang === 'sudah' ? $request->input('gelombang_hari') : null,
            'gelombang_jam'    => $hasGelombang === 'sudah' ? $request->input('gelombang_jam') : null,
            'gelombang_ustadz' => $hasGelombang === 'sudah' ? $request->input('gelombang_ustadz') : null,
        ]);

        return redirect()->route('admin.cabang.index')->with('success', 'Cabang baru berhasil ditambahkan.');
    }

    public function update(Request $request, int $id)
    {
        $cabang = Cabang::findOrFail($id);

        $request->validate([
            'wilayah_id'       => 'required|integer|min:1',
            'name'             => 'required|min:3|max:100',
            'code'             => 'nullable|max:50',
            'alamat'           => 'nullable',
            'maps_url'         => 'nullable|max:500',
            'pimpinan_nama'    => 'nullable|max:100',
            'no_wa'            => 'nullable|max:20',
            'has_gelombang'    => 'required|in:sudah,belum',
            'gelombang_hari'   => 'nullable|max:100',
            'gelombang_jam'    => 'nullable|max:50',
            'gelombang_ustadz' => 'nullable|max:150',
            'description'      => 'nullable',
        ]);

        $hasGelombang = $request->input('has_gelombang') === 'sudah' ? 'sudah' : 'belum';

        $cabang->update([
            'wilayah_id'       => (int) $request->input('wilayah_id'),
            'code'             => $request->input('code') ? strtoupper(trim((string) $request->input('code'))) : null,
            'name'             => trim((string) $request->input('name')),
            'description'      => $request->input('description'),
            'alamat'           => $request->input('alamat'),
            'maps_url'         => $request->input('maps_url'),
            'pimpinan_nama'    => $request->input('pimpinan_nama'),
            'no_wa'            => $request->input('no_wa'),
            'has_gelombang'    => $hasGelombang,
            'gelombang_hari'   => $hasGelombang === 'sudah' ? $request->input('gelombang_hari') : null,
            'gelombang_jam'    => $hasGelombang === 'sudah' ? $request->input('gelombang_jam') : null,
            'gelombang_ustadz' => $hasGelombang === 'sudah' ? $request->input('gelombang_ustadz') : null,
        ]);

        return redirect()->route('admin.cabang.index')->with('success', 'Data cabang berhasil diperbarui.');
    }

    public function delete(int $id)
    {
        $cabang = Cabang::findOrFail($id);

        $pemudaCount = Pemuda::where('cabang_id', $id)->count();
        if ($pemudaCount > 0) {
            return redirect()->back()->with('error', "Tidak dapat menghapus cabang ini karena masih terdapat {$pemudaCount} data pemuda yang terdaftar.");
        }

        $userCount = User::where('cabang_id', $id)->count();
        if ($userCount > 0) {
            return redirect()->back()->with('error', "Tidak dapat menghapus cabang ini karena masih terdapat {$userCount} akun admin cabang yang terhubung.");
        }

        $cabang->delete();
        return redirect()->route('admin.cabang.index')->with('success', 'Cabang berhasil dihapus.');
    }
}
