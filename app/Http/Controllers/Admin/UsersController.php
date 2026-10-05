<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cabang;
use App\Models\Pemuda;
use App\Models\User;
use App\Models\UserRole;
use App\Models\Wilayah;
use App\Services\MtaApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsersController extends Controller
{
    protected MtaApiService $apiService;

    public function __construct(MtaApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    public function index(Request $request)
    {
        $search = $request->input('search');
        $roleId = $request->input('role_id');

        $query = User::with(['role', 'wilayah', 'cabang', 'pemuda']);

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
            'cabangList'  => Cabang::with('wilayah')->orderBy('name', 'ASC')->get(),
            'search'      => $search,
            'selectedRole'=> $roleId,
            'user'        => session()->all(),
        ]);
    }

    /**
     * Pencarian Gabungan (Seamless) Profil Pemuda Sragen & Warga MTA Pusat
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

        $existingUserPemudaIds = User::whereNotNull('pemuda_id')->pluck('pemuda_id')->all();
        $existingUserWargaUuids = User::whereNotNull('mta_warga_uuid')->pluck('mta_warga_uuid')->all();

        // 1. Cari Pemuda Lokal Sragen
        $pemudaQuery = Pemuda::with(['cabang.wilayah'])
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
            $hasAccount = in_array($p->id, $existingUserPemudaIds, true)
                || ($p->mta_warga_uuid && in_array($p->mta_warga_uuid, $existingUserWargaUuids, true));

            if (!empty($p->mta_warga_uuid)) {
                $seenMtaUuids[] = $p->mta_warga_uuid;
            }

            $results[] = [
                'sumber_data'  => 'pemuda',
                'sumber_label' => 'Pemuda MTA Sragen',
                'id'           => $p->id,
                'uuid'         => $p->mta_warga_uuid,
                'nama'         => $p->name,
                'gender'       => $p->gender,
                'gender_label' => $p->gender === 'L' ? 'Ikhwan' : 'Akhwat',
                'cabang_id'    => $p->cabang_id,
                'cabang_name'  => $p->cabang?->name ?? '-',
                'wilayah_id'   => $p->cabang?->wilayah_id ?? null,
                'wilayah_name' => $p->cabang?->wilayah?->name ?? '-',
                'email'        => $p->email,
                'phone'        => $p->phone,
                'reg_no'       => $p->registration_number,
                'status_label' => 'Pemuda Terdata',
                'has_account'  => $hasAccount,
            ];
        }

        // 2. Cari Warga MTA Pusat (Jika query minimal 3 karakter)
        if (mb_strlen($q) >= 3) {
            try {
                $res = $this->apiService->searchWarga($q, ['limit' => 15]);

                if (($res['success'] ?? false) && !empty($res['data'])) {
                    $cabangs = Cabang::with('wilayah')->get(['id', 'name', 'wilayah_id', 'mta_uuid']);

                    foreach ($res['data'] as $w) {
                        $uuid = $w['uuid'] ?? ($w['id'] ?? null);

                        // Hindari duplikasi jika warga ini sudah ada dalam daftar pemuda yang ditemukan
                        if ($uuid && in_array($uuid, $seenMtaUuids, true)) {
                            continue;
                        }

                        $hasAccount = $uuid ? in_array($uuid, $existingUserWargaUuids, true) : false;

                        $cabangName = $w['cabang'] ?? ($w['cabang_nama'] ?? null);
                        $cabangUuid = $w['cabang_uuid'] ?? null;
                        $matchedCabangId = null;
                        $matchedWilayahId = null;
                        $matchedWilayahName = null;

                        if ($cabangUuid) {
                            $c = $cabangs->firstWhere('mta_uuid', $cabangUuid);
                            if ($c) {
                                $matchedCabangId    = $c->id;
                                $cabangName         = $c->name;
                                $matchedWilayahId   = $c->wilayah_id;
                                $matchedWilayahName = $c->wilayah?->name;
                            }
                        }

                        if (!$matchedCabangId && $cabangName) {
                            $c = $cabangs->first(fn($item) => strtolower($item->name) === strtolower($cabangName));
                            if ($c) {
                                $matchedCabangId    = $c->id;
                                $matchedWilayahId   = $c->wilayah_id;
                                $matchedWilayahName = $c->wilayah?->name;
                            }
                        }

                        $results[] = [
                            'sumber_data'  => 'warga',
                            'sumber_label' => 'Warga MTA Pusat',
                            'id'           => null,
                            'uuid'         => $uuid,
                            'nama'         => $w['nama'] ?? ($w['name'] ?? '-'),
                            'gender'       => null,
                            'gender_label' => null,
                            'cabang_id'    => $matchedCabangId,
                            'cabang_name'  => $cabangName ?? '-',
                            'wilayah_id'   => $matchedWilayahId,
                            'wilayah_name' => $matchedWilayahName ?? '-',
                            'email'        => $w['email'] ?? null,
                            'phone'        => $w['telepon'] ?? ($w['hp'] ?? ($w['no_wa'] ?? null)),
                            'reg_no'       => null,
                            'status_label' => $w['status'] ?? ($w['status_warga'] ?? 'Warga MTA'),
                            'has_account'  => $hasAccount,
                        ];
                    }
                }
            } catch (\Throwable) {
                // Fail-safe: jika API pusat offline/timeout, pencarian lokal pemuda tetap berhasil
            }
        }

        return response()->json([
            'status' => 'success',
            'data'   => $results,
        ]);
    }

    /**
     * Pencarian Data Pemuda Sragen untuk dijadikan User
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

        $existingUserPemudaIds = User::whereNotNull('pemuda_id')->pluck('pemuda_id')->all();

        $query = Pemuda::with(['cabang.wilayah'])
            ->where('status_data', '!=', 'archived');

        $s = '%' . $q . '%';
        $query->where(function ($query) use ($s) {
            $query->where('name', 'LIKE', $s)
                  ->orWhere('phone', 'LIKE', $s)
                  ->orWhere('registration_number', 'LIKE', $s);
        });

        $pemuda = $query->limit(20)->get();

        $results = $pemuda->map(function ($p) use ($existingUserPemudaIds) {
            $hasAccount = in_array($p->id, $existingUserPemudaIds, true);

            return [
                'id'            => $p->id,
                'nama'          => $p->name,
                'gender'        => $p->gender,
                'gender_label'  => $p->gender === 'L' ? 'Ikhwan' : 'Akhwat',
                'cabang_id'     => $p->cabang_id,
                'cabang_name'   => $p->cabang?->name ?? '-',
                'wilayah_id'    => $p->cabang?->wilayah_id ?? null,
                'wilayah_name'  => $p->cabang?->wilayah?->name ?? '-',
                'email'         => $p->email,
                'phone'         => $p->phone,
                'reg_no'        => $p->registration_number,
                'mta_uuid'      => $p->mta_warga_uuid,
                'has_account'   => $hasAccount,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data'   => $results,
        ]);
    }

    /**
     * Pencarian Data Warga MTA Pusat untuk dijadikan User
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

        $existingUserWargaUuids = User::whereNotNull('mta_warga_uuid')->pluck('mta_warga_uuid')->all();
        $cabangs = Cabang::with('wilayah')->get(['id', 'name', 'wilayah_id', 'mta_uuid']);

        $results = collect($res['data'])->map(function ($w) use ($cabangs, $existingUserWargaUuids) {
            $uuid = $w['uuid'] ?? ($w['id'] ?? null);
            $hasAccount = $uuid ? in_array($uuid, $existingUserWargaUuids, true) : false;

            $cabangName = $w['cabang'] ?? ($w['cabang_nama'] ?? null);
            $cabangUuid = $w['cabang_uuid'] ?? null;
            $matchedCabangId = null;
            $matchedWilayahId = null;
            $matchedWilayahName = null;

            if ($cabangUuid) {
                $c = $cabangs->firstWhere('mta_uuid', $cabangUuid);
                if ($c) {
                    $matchedCabangId    = $c->id;
                    $cabangName         = $c->name;
                    $matchedWilayahId   = $c->wilayah_id;
                    $matchedWilayahName = $c->wilayah?->name;
                }
            }

            if (!$matchedCabangId && $cabangName) {
                $c = $cabangs->first(fn($item) => strtolower($item->name) === strtolower($cabangName));
                if ($c) {
                    $matchedCabangId    = $c->id;
                    $matchedWilayahId   = $c->wilayah_id;
                    $matchedWilayahName = $c->wilayah?->name;
                }
            }

            return [
                'uuid'         => $uuid,
                'nama'         => $w['nama'] ?? ($w['name'] ?? '-'),
                'cabang_id'    => $matchedCabangId,
                'cabang_name'  => $cabangName ?? '-',
                'wilayah_id'   => $matchedWilayahId,
                'wilayah_name' => $matchedWilayahName ?? '-',
                'email'        => $w['email'] ?? null,
                'phone'        => $w['telepon'] ?? ($w['hp'] ?? ($w['no_wa'] ?? null)),
                'status_warga' => $w['status'] ?? ($w['status_warga'] ?? 'Warga MTA'),
                'has_account'  => $hasAccount,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data'   => $results,
        ]);
    }

    public function simpan(Request $request)
    {
        $request->validate([
            'sumber_data'     => 'required|in:pemuda,warga',
            'pemuda_id'       => 'nullable|required_if:sumber_data,pemuda|integer|exists:pemuda,id',
            'mta_warga_uuid'  => 'nullable|required_if:sumber_data,warga|string|max:36',
            'name'            => 'required|min:3|max:100',
            'email'           => 'required|email|max:100|unique:users,email',
            'username'        => 'required|alpha_dash|min:3|max:50|unique:users,username',
            'password'        => 'required|min:6',
            'role_id'         => 'required|integer|exists:user_roles,id',
        ], [
            'sumber_data.required'       => 'Wajib memilih profil dari Data Pemuda atau Warga MTA.',
            'sumber_data.in'             => 'Sumber profil data pengguna tidak valid.',
            'pemuda_id.required_if'      => 'Silakan pilih pemuda dari hasil pencarian.',
            'mta_warga_uuid.required_if' => 'Silakan pilih warga MTA dari hasil pencarian.',
        ]);

        $sumberData = $request->input('sumber_data');
        $pemudaId   = $request->input('pemuda_id') ? (int) $request->input('pemuda_id') : null;
        $wargaUuid  = $request->input('mta_warga_uuid') ? trim((string) $request->input('mta_warga_uuid')) : null;
        $name       = trim((string) $request->input('name'));

        if ($sumberData === 'pemuda') {
            $pemuda = Pemuda::with('cabang')->findOrFail($pemudaId);
            if (User::where('pemuda_id', $pemuda->id)->exists()) {
                return redirect()->back()->withInput()->with('error', "Pemuda '{$pemuda->name}' sudah memiliki akun pengguna.");
            }
            $name = $pemuda->name;
            if (empty($wargaUuid) && !empty($pemuda->mta_warga_uuid)) {
                $wargaUuid = $pemuda->mta_warga_uuid;
            }
        } elseif ($sumberData === 'warga') {
            if (User::where('mta_warga_uuid', $wargaUuid)->exists()) {
                return redirect()->back()->withInput()->with('error', "Warga MTA tersebut sudah memiliki akun pengguna.");
            }
        }

        $roleId    = (int) $request->input('role_id');
        $wilayahId = $request->input('wilayah_id') ? (int) $request->input('wilayah_id') : null;
        $cabangId  = $request->input('cabang_id') ? (int) $request->input('cabang_id') : null;

        $targetRole = UserRole::find($roleId);
        $roleName   = $targetRole->name ?? '';

        if ($roleName === 'superadmin' || in_array($roleName, ['admin_pemuda', 'admin_pemudi', 'koordinator_gdm'], true)) {
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
            'name'           => $name,
            'email'          => trim((string) $request->input('email')),
            'username'       => strtolower(trim((string) $request->input('username'))),
            'password'       => Hash::make((string) $request->input('password')),
            'role_id'        => $roleId,
            'wilayah_id'     => $wilayahId,
            'cabang_id'      => $cabangId,
            'pemuda_id'      => $pemudaId,
            'mta_warga_uuid' => $wargaUuid,
            'sumber_data'    => $sumberData,
            'status'         => (int) ($request->input('status', 1)),
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

        if ($roleName === 'superadmin' || in_array($roleName, ['admin_pemuda', 'admin_pemudi', 'koordinator_gdm'], true)) {
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
