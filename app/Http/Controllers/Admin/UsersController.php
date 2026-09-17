<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserRole;
use App\Models\Wilayah;
use App\Models\Cabang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsersController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $roleId = $request->input('role_id');

        $query = User::with(['role', 'wilayah', 'cabang']);

        if (!empty($roleId)) {
            $query->where('role_id', (int) $roleId);
        }

        if (!empty($search)) {
            $s = '%' . trim($search) . '%';
            $query->where(function ($q) use ($s) {
                $q->where('name', 'LIKE', $s)
                  ->orWhere('username', 'LIKE', $s)
                  ->orWhere('email', 'LIKE', $s);
            });
        }

        $users = $query->orderBy('id', 'ASC')->get();

        return view('admin.users.index', [
            'title'       => 'Manajemen Pengguna & Admin',
            'users'       => $users,
            'roles'       => UserRole::all(),
            'wilayahList' => Wilayah::orderBy('id', 'ASC')->get(),
            'cabangList'  => Cabang::orderBy('name', 'ASC')->get(),
            'search'      => $search,
            'selectedRole'=> $roleId,
            'user'        => session()->all(),
        ]);
    }

    public function simpan(Request $request)
    {
        $request->validate([
            'name'     => 'required|min:3|max:100',
            'email'    => 'required|email|max:100|unique:users,email',
            'username' => 'required|alpha_dash|min:3|max:50|unique:users,username',
            'password' => 'required|min:6',
            'role_id'  => 'required|integer|exists:user_roles,id',
        ]);

        $roleId    = (int) $request->input('role_id');
        $wilayahId = $request->input('wilayah_id') ? (int) $request->input('wilayah_id') : null;
        $cabangId  = $request->input('cabang_id') ? (int) $request->input('cabang_id') : null;

        $targetRole = UserRole::find($roleId);
        $roleName   = $targetRole->name ?? '';

        if ($roleName === 'superadmin' || in_array($roleName, ['admin_pemuda', 'admin_pemudi'], true)) {
            $wilayahId = null;
            $cabangId  = null;
        } elseif (in_array($roleName, ['admin_wilayah', 'admin_wilayah_pemuda'], true)) {
            if (empty($wilayahId)) {
                return redirect()->back()->withInput()->with('error', 'Admin Wilayah wajib memilih Wilayah yang dikelola.');
            }
            $cabangId = null;
        } elseif ($roleName === 'admin_cabang') {
            if (empty($cabangId)) {
                return redirect()->back()->withInput()->with('error', 'Admin Cabang wajib memilih Cabang yang dikelola.');
            }
            $targetCabang = Cabang::find($cabangId);
            $wilayahId = $targetCabang ? (int) $targetCabang->wilayah_id : null;
        }

        User::create([
            'name'       => trim((string) $request->input('name')),
            'email'      => trim((string) $request->input('email')),
            'username'   => strtolower(trim((string) $request->input('username'))),
            'password'   => Hash::make((string) $request->input('password')),
            'role_id'    => $roleId,
            'wilayah_id' => $wilayahId,
            'cabang_id'  => $cabangId,
            'status'     => (int) ($request->input('status', 1)),
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function update(Request $request, int $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name'     => 'required|min:3|max:100',
            'email'    => "required|email|max:100|unique:users,email,{$id}",
            'username' => "required|alpha_dash|min:3|max:50|unique:users,username,{$id}",
            'role_id'  => 'required|integer|exists:user_roles,id',
            'password' => 'nullable|min:6',
        ]);

        $roleId    = (int) $request->input('role_id');
        $wilayahId = $request->input('wilayah_id') ? (int) $request->input('wilayah_id') : null;
        $cabangId  = $request->input('cabang_id') ? (int) $request->input('cabang_id') : null;

        $targetRole = UserRole::find($roleId);
        $roleName   = $targetRole->name ?? '';

        if ($roleName === 'superadmin' || in_array($roleName, ['admin_pemuda', 'admin_pemudi'], true)) {
            $wilayahId = null;
            $cabangId  = null;
        } elseif (in_array($roleName, ['admin_wilayah', 'admin_wilayah_pemuda'], true)) {
            if (empty($wilayahId)) {
                return redirect()->back()->withInput()->with('error', 'Admin Wilayah wajib memilih Wilayah yang dikelola.');
            }
            $cabangId = null;
        } elseif ($roleName === 'admin_cabang') {
            if (empty($cabangId)) {
                return redirect()->back()->withInput()->with('error', 'Admin Cabang wajib memilih Cabang yang dikelola.');
            }
            $targetCabang = Cabang::find($cabangId);
            $wilayahId = $targetCabang ? (int) $targetCabang->wilayah_id : null;
        }

        // Security guards for current user and superadmin account
        if ($user->id === auth()->id()) {
            if ((int) $request->input('status', 1) !== 1) {
                return redirect()->back()->withInput()->with('error', 'Anda tidak dapat menonaktifkan akun yang sedang digunakan.');
            }
            if ($user->isSuperadmin() && $roleName !== 'superadmin') {
                $superadminCount = User::where('role_id', 1)->where('status', 1)->count();
                if ($superadminCount <= 1) {
                    return redirect()->back()->withInput()->with('error', 'Tidak dapat mengubah role karena Anda adalah satu-satunya Superadmin aktif di sistem.');
                }
            }
        }

        $updateData = [
            'name'       => trim((string) $request->input('name')),
            'email'      => trim((string) $request->input('email')),
            'username'   => strtolower(trim((string) $request->input('username'))),
            'role_id'    => $roleId,
            'wilayah_id' => $wilayahId,
            'cabang_id'  => $cabangId,
            'status'     => (int) ($request->input('status', 1)),
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make((string) $request->input('password'));
        }

        $user->update($updateData);

        return redirect()->route('admin.users.index')->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function delete(int $id)
    {
        if (auth()->id() === $id || session('user_id') === $id) {
            return redirect()->back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user = User::findOrFail($id);
        if ($user->isSuperadmin()) {
            $superadminCount = User::where('role_id', 1)->where('status', 1)->count();
            if ($superadminCount <= 1) {
                return redirect()->back()->with('error', 'Tidak dapat menghapus akun ini karena merupakan satu-satunya Superadmin aktif di sistem.');
            }
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil dihapus.');
    }
}
