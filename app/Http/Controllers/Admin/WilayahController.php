<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Wilayah;
use App\Models\Cabang;
use App\Models\Pemuda;
use App\Models\User;
use Illuminate\Http\Request;

class WilayahController extends Controller
{
    public function index()
    {
        $wilayahList = Wilayah::orderBy('id', 'ASC')->get();

        foreach ($wilayahList as $w) {
            $w->total_cabang = Cabang::where('wilayah_id', $w->id)->count();
            $w->total_pemuda = Pemuda::whereHas('cabang', function ($q) use ($w) {
                $q->where('wilayah_id', $w->id);
            })->count();
        }

        return view('admin.wilayah.index', [
            'title'       => 'Manajemen Wilayah',
            'wilayahList' => $wilayahList,
            'user'        => session()->all(),
        ]);
    }

    public function simpan(Request $request)
    {
        $request->validate([
            'code' => 'required|min:2|max:50|unique:wilayah,code',
            'name' => 'required|min:3|max:100',
        ], [
            'code.unique'   => 'Kode wilayah sudah digunakan.',
            'code.required' => 'Kode wilayah wajib diisi.',
            'name.required' => 'Nama wilayah wajib diisi.',
        ]);

        Wilayah::create([
            'code'        => strtoupper((string) $request->input('code')),
            'name'        => $request->input('name'),
            'description' => $request->input('description'),
        ]);

        return redirect()->route('admin.wilayah.index')->with('success', 'Wilayah berhasil ditambahkan.');
    }

    public function update(Request $request, int $id)
    {
        $request->validate([
            'code' => "required|min:2|max:50|unique:wilayah,code,{$id}",
            'name' => 'required|min:3|max:100',
        ], [
            'code.unique'   => 'Kode wilayah sudah digunakan.',
            'code.required' => 'Kode wilayah wajib diisi.',
            'name.required' => 'Nama wilayah wajib diisi.',
        ]);

        $wilayah = Wilayah::findOrFail($id);
        $wilayah->update([
            'code'        => strtoupper((string) $request->input('code')),
            'name'        => $request->input('name'),
            'description' => $request->input('description'),
        ]);

        return redirect()->route('admin.wilayah.index')->with('success', 'Wilayah berhasil diperbarui.');
    }

    public function delete(int $id)
    {
        $wilayah = Wilayah::findOrFail($id);

        $cabangCount = Cabang::where('wilayah_id', $id)->count();
        if ($cabangCount > 0) {
            return redirect()->back()->with('error', "Tidak dapat menghapus wilayah ini karena masih memiliki {$cabangCount} cabang aktif.");
        }

        $userCount = User::where('wilayah_id', $id)->count();
        if ($userCount > 0) {
            return redirect()->back()->with('error', "Tidak dapat menghapus wilayah ini karena masih terdapat {$userCount} akun admin yang terhubung.");
        }

        $wilayah->delete();
        return redirect()->route('admin.wilayah.index')->with('success', 'Wilayah berhasil dihapus.');
    }
}
