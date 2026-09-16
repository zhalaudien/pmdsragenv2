<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Cabang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller
{
    public function login()
    {
        if (Auth::check() || session()->has('user_id')) {
            return redirect()->route('admin.dashboard');
        }

        return view('auth.login');
    }

    public function authenticate(Request $request)
    {
        $ip = $request->ip();
        $throttleKey = 'admin_login_' . $ip;

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return redirect()->back()
                ->withInput()
                ->with('error', "Terlalu banyak percobaan login. Demi keamanan, silakan tunggu {$seconds} detik sebelum mencoba lagi.");
        }

        $request->validate([
            'login'    => 'required|min:3',
            'password' => 'required|min:5',
        ], [
            'login.required'    => 'Username atau email wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $loginInput = trim((string) $request->input('login'));
        $password   = (string) $request->input('password');

        $user = User::with(['role', 'wilayah', 'cabang'])
            ->where('username', $loginInput)
            ->orWhere('email', $loginInput)
            ->first();

        if (!$user || !Hash::check($password, $user->password)) {
            RateLimiter::hit($throttleKey, 60);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Username / Email atau Password tidak sesuai.');
        }

        if ((int) $user->status !== 1) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Akun Anda dinonaktifkan. Silakan hubungi Administrator.');
        }

        RateLimiter::clear($throttleKey);
        Auth::login($user);
        $request->session()->regenerate();

        $roleName    = $user->role->name ?? '';
        $wilayahId   = $user->wilayah_id;
        $wilayahName = $user->wilayah->name ?? null;
        $cabangId    = $user->cabang_id;
        $cabangName  = $user->cabang->name ?? null;

        if (in_array($roleName, ['superadmin', 'admin_pemuda', 'admin_pemudi'], true)) {
            $wilayahId   = null;
            $wilayahName = null;
            $cabangId    = null;
            $cabangName  = null;
        } elseif ($roleName === 'admin_cabang' && $cabangId && !$wilayahId) {
            $cabangData = Cabang::with('wilayah')->find($cabangId);
            if ($cabangData) {
                $wilayahId   = $cabangData->wilayah_id;
                $wilayahName = $cabangData->wilayah->name ?? null;
            }
        }

        session([
            'user_id'          => $user->id,
            'name'             => $user->name,
            'email'            => $user->email,
            'username'         => $user->username,
            'role'             => $roleName,
            'role_id'          => $user->role_id,
            'role_description' => $user->role->description ?? '',
            'wilayah_id'       => $wilayahId,
            'wilayah_name'     => $wilayahName,
            'cabang_id'        => $cabangId,
            'cabang_name'      => $cabangName,
            'is_logged_in'     => true,
        ]);

        $user->update(['last_login' => now()]);

        $redirectUrl = session()->pull('redirect_url', route('admin.dashboard'));

        return redirect()->to($redirectUrl)
            ->with('success', 'Selamat datang kembali, ' . $user->name . '!');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Anda telah berhasil keluar dari sistem.');
    }
}
