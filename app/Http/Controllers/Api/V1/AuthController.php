<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\ApiSetting;
use App\Models\KegiatanPerwakilan;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends BaseApiController
{
    /**
     * POST /api/v1/auth/login
     * Login petugas cabang / admin
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'login'       => 'required|string',
            'password'    => 'required|string',
            'device_name' => 'nullable|string|max:100',
        ], [
            'login.required'    => 'Username atau email wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validasi gagal.', 422, $validator->errors()->toArray());
        }

        $loginInput = trim($request->input('login'));
        $password   = $request->input('password');

        // Cari berdasarkan username atau email
        $user = User::with(['role', 'cabang.wilayah'])
            ->where(function ($query) use ($loginInput) {
                $query->where('username', $loginInput)
                      ->orWhere('email', $loginInput);
            })
            ->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return $this->errorResponse('Kredensial login (username/password) tidak cocok.', 401, [
                'auth' => ['Username atau kata sandi yang Anda masukkan salah.'],
            ]);
        }

        // Cek status aktif user
        if ($user->status !== 1 && $user->status !== true) {
            return $this->errorResponse('Akun Anda telah dinonaktifkan. Silakan hubungi administrator.', 403, [
                'status' => ['Akun tidak aktif.'],
            ]);
        }

        // Validasi kaitan cabang jika bukan superadmin
        if (!$user->isSuperadmin() && empty($user->cabang_id)) {
            return $this->errorResponse('Akun Anda belum memiliki cabang penugasan.', 403, [
                'cabang' => ['Akun tidak memiliki cabang_id yang valid.'],
            ]);
        }

        // Generate token Sanctum
        $deviceName = $request->input('device_name') ?: ($request->header('User-Agent') ?: 'Presensi PMD Mobile');
        $token = $user->createToken($deviceName)->plainTextToken;

        // Update last login
        $user->update(['last_login' => now()]);

        $responseData = [
            'token'      => $token,
            'token_type' => 'Bearer',
            'user'       => [
                'id'         => $user->id,
                'name'       => $user->name,
                'email'      => $user->email,
                'username'   => $user->username,
                'role'       => $user->role_name,
                'role_id'    => $user->role_id,
                'cabang_id'  => $user->cabang_id,
                'wilayah_id' => $user->wilayah_id,
            ],
            'cabang' => $user->cabang ? [
                'id'               => $user->cabang->id,
                'code'             => $user->cabang->code,
                'name'             => $user->cabang->name,
                'alamat'           => $user->cabang->alamat,
                'pimpinan_nama'    => $user->cabang->pimpinan_nama,
                'no_wa'            => $user->cabang->no_wa,
                'has_gelombang'    => $user->cabang->has_gelombang,
                'gelombang_hari'   => $user->cabang->gelombang_hari,
                'gelombang_jam'    => $user->cabang->gelombang_jam,
                'gelombang_ustadz' => $user->cabang->gelombang_ustadz,
                'wilayah'          => $user->cabang->wilayah?->name,
            ] : null,
            'app_config' => [
                'min_app_version'    => ApiSetting::get('api_min_app_version', '1.0.0'),
                'latest_app_version' => ApiSetting::get('api_latest_app_version', '1.0.0'),
                'apk_download_url'   => ApiSetting::get('api_apk_download_url', ''),
                'broadcast_message'  => ApiSetting::get('api_broadcast_message', ''),
                'allow_offline_sync' => (bool)(int)ApiSetting::get('api_allow_offline_sync', '1'),
                'max_bulk_sync'      => (int)ApiSetting::get('api_max_bulk_sync', '300'),
                'quick_chips'        => json_decode(ApiSetting::get('api_quick_chips', '[]'), true) ?: [],
            ],
        ];

        return $this->successResponse($responseData, 'Login berhasil. Selamat bertugas.');
    }

    /**
     * POST /api/v1/auth/logout
     * Revoke token yang sedang digunakan
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user) {
            $user->currentAccessToken()->delete();
        }

        return $this->successResponse(null, 'Logout berhasil. Sesi telah diakhiri.');
    }

    /**
     * GET /api/v1/auth/me
     * Ambil data profil user, cabang, dan konfigurasi aplikasi
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load(['role', 'cabang.wilayah']);

        $data = [
            'user' => [
                'id'         => $user->id,
                'name'       => $user->name,
                'email'      => $user->email,
                'username'   => $user->username,
                'role'       => $user->role_name,
                'role_id'    => $user->role_id,
                'cabang_id'  => $user->cabang_id,
                'wilayah_id' => $user->wilayah_id,
            ],
            'cabang' => $user->cabang ? [
                'id'               => $user->cabang->id,
                'code'             => $user->cabang->code,
                'name'             => $user->cabang->name,
                'alamat'           => $user->cabang->alamat,
                'pimpinan_nama'    => $user->cabang->pimpinan_nama,
                'no_wa'            => $user->cabang->no_wa,
                'has_gelombang'    => $user->cabang->has_gelombang,
                'gelombang_hari'   => $user->cabang->gelombang_hari,
                'gelombang_jam'    => $user->cabang->gelombang_jam,
                'gelombang_ustadz' => $user->cabang->gelombang_ustadz,
                'wilayah'          => $user->cabang->wilayah?->name,
            ] : null,
            'app_config' => [
                'min_app_version'    => ApiSetting::get('api_min_app_version', '1.0.0'),
                'latest_app_version' => ApiSetting::get('api_latest_app_version', '1.0.0'),
                'apk_download_url'   => ApiSetting::get('api_apk_download_url', ''),
                'broadcast_message'  => ApiSetting::get('api_broadcast_message', ''),
                'allow_offline_sync' => (bool)(int)ApiSetting::get('api_allow_offline_sync', '1'),
                'max_bulk_sync'      => (int)ApiSetting::get('api_max_bulk_sync', '300'),
                'quick_chips'        => json_decode(ApiSetting::get('api_quick_chips', '[]'), true) ?: [],
            ],
        ];

        return $this->successResponse($data, 'Profil berhasil diambil.');
    }

    /**
     * PUT /api/v1/auth/password
     * Ubah kata sandi user
     */
    public function updatePassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'current_password'      => 'required|string',
            'new_password'          => 'required|string|min:6|confirmed',
        ], [
            'current_password.required' => 'Kata sandi saat ini wajib diisi.',
            'new_password.required'     => 'Kata sandi baru wajib diisi.',
            'new_password.min'          => 'Kata sandi baru minimal 6 karakter.',
            'new_password.confirmed'    => 'Konfirmasi kata sandi baru tidak cocok.',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validasi gagal.', 422, $validator->errors()->toArray());
        }

        $user = $request->user();
        if (!Hash::check($request->input('current_password'), $user->password)) {
            return $this->errorResponse('Kata sandi saat ini tidak cocok.', 422, [
                'current_password' => ['Kata sandi saat ini salah.'],
            ]);
        }

        $user->update([
            'password' => Hash::make($request->input('new_password')),
        ]);

        return $this->successResponse(null, 'Kata sandi berhasil diperbarui.');
    }

    /**
     * PUT /api/v1/auth/profile
     * Ubah profil user login (nama, email)
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();
        $validator = Validator::make($request->all(), [
            'name'  => 'required|string|max:100',
            'email' => 'nullable|email|max:100|unique:users,email,' . $user->id,
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.email'   => 'Format email tidak valid.',
            'email.unique'  => 'Email sudah digunakan oleh akun lain.',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validasi gagal.', 422, $validator->errors()->toArray());
        }

        $user->update([
            'name'  => $request->input('name'),
            'email' => $request->input('email'),
        ]);

        return $this->successResponse([
            'user' => [
                'id'         => $user->id,
                'name'       => $user->name,
                'email'      => $user->email,
                'username'   => $user->username,
                'role'       => $user->role_name,
                'role_id'    => $user->role_id,
                'cabang_id'  => $user->cabang_id,
                'wilayah_id' => $user->wilayah_id,
            ]
        ], 'Profil berhasil diperbarui.');
    }

    /**
     * GET /api/v1/config
     * Konfigurasi umum aplikasi mobile (Publik)
     */
    public function config(): JsonResponse
    {
        $enabled = (int)ApiSetting::get('api_presensi_enabled', '1') === 1;

        $data = [
            'is_online'          => $enabled,
            'maintenance_mode'   => !$enabled,
            'maintenance_message'=> ApiSetting::get('api_maintenance_message', ''),
            'min_app_version'    => ApiSetting::get('api_min_app_version', '1.0.0'),
            'latest_app_version' => ApiSetting::get('api_latest_app_version', '1.0.0'),
            'apk_download_url'   => ApiSetting::get('api_apk_download_url', ''),
            'broadcast_message'  => ApiSetting::get('api_broadcast_message', ''),
            'allow_offline_sync' => (bool)(int)ApiSetting::get('api_allow_offline_sync', '1'),
            'max_bulk_sync'      => (int)ApiSetting::get('api_max_bulk_sync', '300'),
            'quick_chips'        => json_decode(ApiSetting::get('api_quick_chips', '[]'), true) ?: [],
        ];

        return $this->successResponse($data, 'Konfigurasi aplikasi mobile.');
    }

    /**
     * GET /api/v1/perwakilan/kegiatan
     * Daftar kegiatan pemuda perwakilan yang aktif & akan datang
     */
    public function kegiatanPerwakilan(Request $request): JsonResponse
    {
        KegiatanPerwakilan::seedDefaults();

        $query = KegiatanPerwakilan::where('is_active', true);

        if ($request->filled('kategori') && $request->kategori !== 'semua') {
            $query->where('kategori', $request->kategori);
        }

        $items = $query->orderBy('tanggal', 'ASC')->get();

        $data = $items->map(function ($item) {
            return [
                'id'                => $item->id,
                'nama_kegiatan'     => $item->nama_kegiatan,
                'kategori'          => $item->kategori,
                'tanggal'           => $item->tanggal ? $item->tanggal->format('Y-m-d') : '',
                'hari_tanggal'      => $item->hari_tanggal,
                'jam'               => $item->jam,
                'lokasi'            => $item->lokasi,
                'alamat_detail'     => $item->alamat_detail,
                'pemateri'          => $item->pemateri,
                'target_peserta'    => $item->target_peserta,
                'penyelenggara'     => $item->penyelenggara,
                'deskripsi'         => $item->deskripsi,
                'catatan_ketentuan' => $item->catatan_ketentuan,
                'narahubung'        => $item->narahubung,
                'status'            => $item->status,
                'hari_tersisa'      => $item->hari_tersisa,
            ];
        });

        return $this->successResponse($data, 'Daftar kegiatan pemuda perwakilan.');
    }
}
