<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Tampilkan halaman profil & pengaturan akun
     */
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $user->load([
            'role',
            'wilayah',
            'cabang.wilayah',
            'pemuda.cabang',
            'pemuda.alamat.province',
            'pemuda.alamat.regency',
            'pemuda.alamat.district',
            'pemuda.alamat.village',
            'pemuda.pendidikan',
            'pemuda.pekerjaan',
        ]);

        return view('admin.profile.index', [
            'title' => 'Profil & Pengaturan Akun',
            'user'  => $user,
        ]);
    }

    /**
     * Update data profil (nama, email, no_wa jika terhubung ke pemuda)
     */
    public function updateProfile(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $request->validate([
            'name'  => 'required|string|min:3|max:100',
            'email' => [
                'required',
                'email',
                'max:100',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'phone' => 'nullable|string|min:8|max:20',
        ], [
            'name.required'  => 'Nama lengkap wajib diisi.',
            'name.min'       => 'Nama lengkap minimal 3 karakter.',
            'name.max'       => 'Nama lengkap maksimal 100 karakter.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email'    => 'Format email tidak valid.',
            'email.unique'   => 'Alamat email sudah digunakan oleh akun lain.',
            'phone.min'      => 'Nomor telepon/WhatsApp minimal 8 digit.',
            'phone.max'      => 'Nomor telepon/WhatsApp maksimal 20 digit.',
        ]);

        $user->name  = trim((string) $request->input('name'));
        $user->email = trim((string) $request->input('email'));
        $user->save();

        // Jika terhubung dengan entitas Pemuda, sinkronkan juga kontak pemuda jika ada
        if ($user->pemuda) {
            $pemudaUpdates = [];
            if ($request->has('phone')) {
                $pemudaUpdates['phone'] = trim((string) $request->input('phone'));
            }
            if ($user->email) {
                $pemudaUpdates['email'] = $user->email;
            }
            if (!empty($pemudaUpdates)) {
                $user->pemuda->update($pemudaUpdates);
            }
        }

        // Perbarui data session
        session([
            'name'  => $user->name,
            'email' => $user->email,
        ]);

        return redirect()->route('admin.profile.index')
            ->with('success', 'Profil Anda berhasil diperbarui.');
    }

    /**
     * Ganti username akun
     */
    public function updateUsername(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $request->validate([
            'username' => [
                'required',
                'string',
                'min:3',
                'max:50',
                'alpha_dash',
                Rule::unique('users', 'username')->ignore($user->id),
            ],
            'current_password' => 'required|string',
        ], [
            'username.required'         => 'Username baru wajib diisi.',
            'username.min'              => 'Username minimal 3 karakter.',
            'username.max'              => 'Username maksimal 50 karakter.',
            'username.alpha_dash'       => 'Username hanya boleh berisi huruf, angka, tanda strip (-), dan garis bawah (_).',
            'username.unique'           => 'Username sudah digunakan oleh akun lain.',
            'current_password.required' => 'Password saat ini wajib diisi untuk konfirmasi keamanan.',
        ]);

        // Verifikasi kata sandi saat ini
        if (!Hash::check($request->input('current_password'), $user->password)) {
            return redirect()->route('admin.profile.index')
                ->withErrors(['current_password' => 'Password saat ini tidak sesuai. Konfirmasi password diperlukan demi keamanan akun.'])
                ->withInput()
                ->with('active_tab', 'username');
        }

        $newUsername = strtolower(trim((string) $request->input('username')));

        if ($newUsername === strtolower($user->username)) {
            return redirect()->route('admin.profile.index')
                ->with('info', 'Username baru sama dengan username saat ini.')
                ->with('active_tab', 'username');
        }

        $user->username = $newUsername;
        $user->save();

        // Update session
        session(['username' => $user->username]);

        return redirect()->route('admin.profile.index')
            ->with('success', 'Username berhasil diubah menjadi "' . $user->username . '".')
            ->with('active_tab', 'username');
    }

    /**
     * Ganti password akun
     */
    public function updatePassword(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $request->validate([
            'current_password'          => 'required|string',
            'password'                  => 'required|string|min:6|confirmed',
            'password_confirmation'     => 'required|string',
        ], [
            'current_password.required'      => 'Password saat ini wajib diisi.',
            'password.required'              => 'Password baru wajib diisi.',
            'password.min'                   => 'Password baru minimal 6 karakter.',
            'password.confirmed'             => 'Konfirmasi password baru tidak cocok.',
            'password_confirmation.required' => 'Konfirmasi password baru wajib diisi.',
        ]);

        // Verifikasi password saat ini
        if (!Hash::check($request->input('current_password'), $user->password)) {
            return redirect()->route('admin.profile.index')
                ->withErrors(['password_current_error' => 'Password saat ini tidak sesuai.'])
                ->withInput()
                ->with('active_tab', 'password');
        }

        // Cek jika password baru sama dengan password lama
        if (Hash::check($request->input('password'), $user->password)) {
            return redirect()->route('admin.profile.index')
                ->withErrors(['password' => 'Password baru tidak boleh sama dengan password saat ini.'])
                ->withInput()
                ->with('active_tab', 'password');
        }

        $user->password = Hash::make($request->input('password'));
        $user->save();

        return redirect()->route('admin.profile.index')
            ->with('success', 'Password Anda berhasil diperbarui. Silakan gunakan password baru ini saat masuk kembali.')
            ->with('active_tab', 'password');
    }
}
