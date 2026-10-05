@extends('admin.layouts.main')

@section('title', 'Manajemen Pengguna & Admin')

@section('content')

<!-- HEADER JUDUL HALAMAN -->
<div class="mb-5">
    <div class="flex flex-wrap items-center gap-2 mb-2">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-red-500/10 border border-red-500/20 text-red-700 text-xs font-bold">
            <i class="bi bi-shield-lock-fill"></i> Manajemen Pengguna &amp; Akun
        </span>
        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-600 text-xs font-medium">
            RBAC &amp; Autentikasi
        </span>
    </div>
    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Manajemen Pengguna &amp; Akun</h2>
    <p class="text-xs sm:text-sm text-slate-500 mt-1 max-w-3xl">Kelola akun administrator sistem. Akun pengguna ditautkan secara langsung dari profil Pemuda MTA Sragen atau Warga MTA Pusat.</p>
</div>

<!-- TOMBOL MENU & NAVIGASI CEPAT -->
<div class="mb-6 bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3.5 pb-3 border-b border-slate-100">
        <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xl bg-red-50 text-red-600 flex items-center justify-center font-bold text-sm">
                <i class="bi bi-grid-fill"></i>
            </div>
            <div>
                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Tombol Menu &amp; Navigasi Cepat</h3>
                <p class="text-[11px] text-slate-500">Pintasan aksi penambahan akun dan navigasi master data</p>
            </div>
        </div>
        <span class="text-[11px] text-slate-400 hidden sm:inline-flex items-center gap-1.5 font-medium">
            <i class="bi bi-lightning-charge-fill text-amber-500"></i> Menu Cepat
        </span>
    </div>

    <div class="flex flex-wrap items-center gap-2.5">
        <!-- Tambah Pengguna Baru -->
        <button type="button" onclick="openAddUserModal()" class="px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold text-xs transition shadow-sm hover:shadow flex items-center gap-2">
            <i class="bi bi-person-plus-fill text-sm"></i>
            <span>Tambah Pengguna Baru</span>
        </button>

        <!-- Master Cabang -->
        <a href="{{ route('admin.cabang.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-700 hover:text-slate-900 font-semibold text-xs transition border border-slate-200/90 shadow-2xs">
            <i class="bi bi-diagram-3-fill text-slate-500 text-sm"></i>
            <span>Master Cabang</span>
        </a>

        <!-- Master Wilayah -->
        <a href="{{ route('admin.wilayah.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-700 hover:text-slate-900 font-semibold text-xs transition border border-slate-200/90 shadow-2xs">
            <i class="bi bi-geo-alt-fill text-slate-500 text-sm"></i>
            <span>Master Wilayah</span>
        </a>

        <!-- Data Pemuda -->
        <a href="{{ route('admin.pemuda.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-700 hover:text-slate-900 font-semibold text-xs transition border border-slate-200/90 shadow-2xs">
            <i class="bi bi-people-fill text-slate-500 text-sm"></i>
            <span>Data Pemuda</span>
        </a>
    </div>
</div>

<!-- FILTER CARD -->
<div class="mb-6 rounded-3xl bg-white p-5 border border-slate-200/80 shadow-sm">
    <form action="{{ route('admin.users.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs items-end">
        <div>
            <label class="block font-bold text-slate-700 uppercase mb-1">Cari Pengguna</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Nama, username, email..." class="w-full pl-9 pr-3 py-2 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
            </div>
        </div>

        <div>
            <label class="block font-bold text-slate-700 uppercase mb-1">Tingkat Peran (Role)</label>
            <select name="role_id" class="w-full py-2 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                <option value="">-- Semua Role --</option>
                @foreach($roles as $r)
                    <option value="{{ $r->id }}" {{ ($selectedRole ?? '') == $r->id ? 'selected' : '' }}>{{ $r->description ?: $r->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="flex-1 py-2 px-4 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold transition shadow-sm flex items-center justify-center gap-1.5">
                <i class="bi bi-filter"></i> Saring
            </button>
            <a href="{{ route('admin.users.index') }}" class="py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold transition" title="Reset Filter">
                <i class="bi bi-arrow-clockwise"></i>
            </a>
        </div>
    </form>
</div>

<!-- USERS LIST TABLE -->
<div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50 text-slate-700 font-bold uppercase tracking-wider text-[11px]">
                    <th class="py-3 px-4">Nama Pengguna &amp; Profil</th>
                    <th class="py-3 px-4">Username &amp; Email</th>
                    <th class="py-3 px-4">Peran (Role)</th>
                    <th class="py-3 px-4">Lingkup Akses</th>
                    <th class="py-3 px-4 text-center">Status</th>
                    <th class="py-3 px-4 text-center">Login Terakhir</th>
                    <th class="py-3 px-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($users as $u)
                    <tr class="hover:bg-slate-50/75 transition">
                        <td class="py-3 px-4">
                            <div class="font-bold text-slate-900">{{ $u->name }}</div>
                            <div class="mt-1">
                                @if($u->sumber_data === 'pemuda' || $u->pemuda_id)
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200" title="Tersinkron data Pemuda MTA Sragen">
                                        <i class="bi bi-people-fill text-[9px]"></i> Pemuda Sragen
                                    </span>
                                @elseif($u->sumber_data === 'warga' || $u->mta_warga_uuid)
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-semibold bg-sky-50 text-sky-700 border border-sky-200" title="Tersinkron data Warga MTA Pusat">
                                        <i class="bi bi-cloud-check-fill text-[9px]"></i> Warga MTA
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-500" title="Akun Internal/Manual">
                                        <i class="bi bi-shield text-[9px]"></i> Manual
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="py-3 px-4">
                            <div class="font-mono font-semibold text-slate-800">{{ $u->username }}</div>
                            <div class="text-[11px] text-slate-400">{{ $u->email }}</div>
                        </td>
                        <td class="py-3 px-4">
                            @php
                                $roleName = $u->role->name ?? '';
                            @endphp
                            @if($roleName === 'superadmin')
                                <span class="px-2.5 py-0.5 rounded-full bg-red-100 text-red-700 font-bold text-[10px]">Super Administrator</span>
                            @elseif($roleName === 'admin_pemuda')
                                <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-700 font-bold text-[10px]">Admin Pemuda (L)</span>
                            @elseif($roleName === 'admin_pemudi')
                                <span class="px-2.5 py-0.5 rounded-full bg-pink-100 text-pink-700 font-bold text-[10px]">Admin Pemudi (P)</span>
                            @elseif($roleName === 'admin_wilayah' || $roleName === 'admin_wilayah_pemuda')
                                <span class="px-2.5 py-0.5 rounded-full bg-sky-100 text-sky-700 font-bold text-[10px]">{{ $u->role->description ?? 'Admin Wilayah' }}</span>
                            @elseif($roleName === 'koordinator_gdm')
                                <span class="px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-700 font-bold text-[10px]">Koordinator GDM</span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-700 font-semibold text-[10px]">Admin Cabang</span>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            @if($u->cabang)
                                <span class="font-semibold text-slate-800">{{ $u->cabang->name }}</span>
                                <span class="text-[10px] text-slate-400 block">{{ $u->cabang->wilayah->name ?? '' }}</span>
                            @elseif($u->wilayah)
                                <span class="font-semibold text-slate-800">{{ $u->wilayah->name }}</span>
                            @else
                                <span class="text-slate-400">Seluruh Sragen</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center">
                            @if($u->status === 1)
                                <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 font-bold text-[10px]">Aktif</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full bg-red-100 text-red-700 font-bold text-[10px]">Nonaktif</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center text-slate-400 text-[11px]">
                            {{ $u->last_login ? \Carbon\Carbon::parse($u->last_login)->diffForHumans() : 'Belum pernah' }}
                        </td>
                        <td class="py-3 px-4 text-center">
                            <div class="inline-flex items-center gap-1">
                                <button type="button" onclick="editUser({{ json_encode($u) }})" class="p-1.5 rounded-lg bg-slate-100 hover:bg-amber-50 hover:text-amber-600 text-slate-600 transition" title="Edit Akun">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                @if(auth()->id() !== $u->id)
                                    <form action="{{ route('admin.users.delete', $u->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus akun pengguna {{ $u->name }}?')">
                                        @csrf
                                        <button type="submit" class="p-1.5 rounded-lg bg-slate-100 hover:bg-red-50 hover:text-red-600 text-slate-400 transition" title="Hapus Akun">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-400">Tidak ada data pengguna.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- ================================================================ -->
<!-- MODAL TAMBAH USER (BINDING PROFIL DARI PEMUDA ATAU WARGA MTA)    -->
<!-- ================================================================ -->
<div id="modalAddUser" data-modal class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-xl w-full p-6 shadow-2xl border border-slate-100 animate-in fade-in zoom-in-95 max-h-[92vh] overflow-y-auto text-xs">
        <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
            <div>
                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <i class="bi bi-person-plus text-red-600"></i>
                    <span>Tambah Pengguna Baru</span>
                </h3>
                <p class="text-[11px] text-slate-500 mt-0.5">Cari profil calon pengguna dari database Pemuda MTA Sragen atau Warga MTA Pusat dalam satu kolom pencarian.</p>
            </div>
            <button type="button" onclick="closeModal('modalAddUser')" class="text-slate-400 hover:text-slate-600">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <!-- LANGKAH 1: CARI PROFIL CALON PENGGUNA (GABUNGAN PEMUDA SRAGEN & WARGA MTA PUSAT) -->
        <div id="sectionProfilePicker" class="mb-4 p-3.5 bg-slate-50 rounded-2xl border border-slate-200/80">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 mb-2">
                <label class="block font-bold text-slate-800 uppercase text-[10px] tracking-wider">
                    <span class="text-red-500">*</span> Langkah 1: Cari Profil Calon Pengguna
                </label>
                <span class="text-[10px] text-slate-500 font-medium flex items-center gap-1">
                    <i class="bi bi-shield-check text-emerald-600"></i> Otomatis mencari di Pemuda &amp; Warga MTA
                </span>
            </div>

            <div class="relative">
                <i class="bi bi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" id="inputSearchUserUnified" oninput="debounceSearchUserUnified(this.value)" placeholder="Ketik nama, no WhatsApp, atau no registrasi pemuda/warga..." class="w-full pl-9 pr-9 py-2.5 rounded-xl border border-slate-300 bg-white focus:ring-red-500 focus:border-red-500 text-xs shadow-2xs">
                <button type="button" id="btnClearSearchUnified" onclick="clearSearchUserUnified()" class="hidden absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600" title="Bersihkan pencarian">
                    <i class="bi bi-x-circle-fill"></i>
                </button>
            </div>
            <div id="searchResultsUserUnified" class="mt-2.5 max-h-56 overflow-y-auto space-y-1.5 hidden">
                <!-- Populated via AJAX -->
            </div>
        </div>

        <!-- KARTU PROFIL TERPILIH (PREVIEW & LOCK) -->
        <div id="selectedUserProfileCard" class="hidden mb-4 p-3.5 rounded-2xl bg-emerald-50/80 border border-emerald-200 shadow-2xs">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-start gap-2.5">
                    <div id="selectedUserIconContainer" class="w-9 h-9 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-bold text-sm flex-shrink-0 mt-0.5">
                        <i class="bi bi-person-check-fill"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <h4 id="selectedUserNama" class="text-sm font-black text-slate-900 leading-tight">Nama Profil</h4>
                            <span id="selectedUserBadge" class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                Pemuda
                            </span>
                        </div>
                        <div class="text-[11px] text-slate-600 mt-1 flex flex-wrap items-center gap-x-2.5 gap-y-0.5">
                            <span id="selectedUserCabang"><i class="bi bi-diagram-3"></i> Cabang: -</span>
                            <span id="selectedUserWilayah" class="text-slate-400">(-)</span>
                            <span id="selectedUserKontak" class="text-emerald-700 font-semibold"></span>
                        </div>
                    </div>
                </div>
                <button type="button" onclick="resetSelectedUserProfile()" class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-slate-600 hover:text-red-600 hover:border-red-200 font-bold text-[11px] transition shadow-2xs flex-shrink-0" title="Ganti Profil">
                    <i class="bi bi-arrow-repeat"></i> Ganti
                </button>
            </div>
        </div>

        <!-- FORM BUAT AKUN PENGGUNA -->
        <form id="formAddUser" action="{{ route('admin.users.simpan') }}" method="POST" onsubmit="return validateUserCreationForm(event)" class="space-y-3.5">
            @csrf
            <!-- Hidden Fields dari Profil Terpilih -->
            <input type="hidden" name="sumber_data" id="addUserSumberData">
            <input type="hidden" name="pemuda_id" id="addUserPemudaId">
            <input type="hidden" name="mta_warga_uuid" id="addUserMtaUuid">
            <input type="hidden" name="name" id="addUserName">

            <div class="p-3.5 bg-slate-50/70 rounded-2xl border border-slate-200/70 space-y-3">
                <div class="font-bold text-slate-800 uppercase text-[10px] tracking-wider flex items-center gap-1.5">
                    <i class="bi bi-key-fill text-amber-500"></i> Langkah 2: Atur Akses &amp; Kredensial Login
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1 text-[11px]">Username <span class="text-red-500">*</span></label>
                        <input type="text" name="username" id="addUserUsername" required placeholder="username_login" class="w-full py-2 px-3 rounded-xl border border-slate-300 bg-white focus:ring-red-500 focus:border-red-500">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1 text-[11px]">Password <span class="text-red-500">*</span></label>
                        <input type="password" name="password" id="addUserPassword" required placeholder="Minimal 6 karakter" class="w-full py-2 px-3 rounded-xl border border-slate-300 bg-white focus:ring-red-500 focus:border-red-500">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1 text-[11px]">Email Akun <span class="text-red-500">*</span></label>
                    <input type="email" name="email" id="addUserEmail" required placeholder="admin@pmdsragen.id" class="w-full py-2 px-3 rounded-xl border border-slate-300 bg-white focus:ring-red-500 focus:border-red-500">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1 text-[11px]">Peran (Role) <span class="text-red-500">*</span></label>
                    <select name="role_id" id="addRoleId" required class="w-full py-2 px-3 rounded-xl border border-slate-300 bg-white focus:ring-red-500 focus:border-red-500">
                        <option value="">-- Pilih Peran --</option>
                        @foreach($roles as $r)
                            <option value="{{ $r->id }}" data-role="{{ $r->name }}">{{ $r->description ?: $r->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div id="addWilayahWrapper" class="hidden">
                    <label class="block font-bold text-slate-700 uppercase mb-1 text-[11px]">Wilayah yang Dikelola <span class="text-red-500">*</span></label>
                    <select name="wilayah_id" id="addWilayahId" class="w-full py-2 px-3 rounded-xl border border-slate-300 bg-white focus:ring-red-500 focus:border-red-500">
                        <option value="">-- Pilih Wilayah --</option>
                        @foreach($wilayahList as $w)
                            <option value="{{ $w->id }}">{{ $w->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div id="addCabangWrapper" class="hidden">
                    <label class="block font-bold text-slate-700 uppercase mb-1 text-[11px]">Cabang yang Dikelola <span class="text-red-500">*</span></label>
                    <select name="cabang_id" id="addCabangId" class="w-full py-2 px-3 rounded-xl border border-slate-300 bg-white focus:ring-red-500 focus:border-red-500">
                        <option value="">-- Pilih Cabang --</option>
                        @foreach($cabangList as $c)
                            <option value="{{ $c->id }}" data-wilayah="{{ $c->wilayah_id }}">{{ $c->name }} ({{ $c->wilayah?->name ?? '-' }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1 text-[11px]">Status Akun</label>
                    <select name="status" class="w-full py-2 px-3 rounded-xl border border-slate-300 bg-white focus:ring-red-500 focus:border-red-500">
                        <option value="1">Aktif</option>
                        <option value="0">Nonaktif</option>
                    </select>
                </div>
            </div>

            <div class="pt-2 flex items-center justify-end gap-2">
                <button type="button" onclick="closeModal('modalAddUser')" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold transition">Batal</button>
                <button type="submit" id="btnSubmitAddUser" class="px-5 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold transition shadow-md flex items-center gap-1.5">
                    <i class="bi bi-check-lg"></i>
                    <span>Simpan Pengguna</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================================================================ -->
<!-- MODAL EDIT USER                                                  -->
<!-- ================================================================ -->
<div id="modalEditUser" data-modal class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 animate-in fade-in zoom-in-95 text-xs">
        <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <i class="bi bi-pencil-square text-amber-500"></i>
                <span>Edit Pengguna</span>
            </h3>
            <button type="button" onclick="closeModal('modalEditUser')" class="text-slate-400 hover:text-slate-600">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div id="editUserSourceBadgeContainer" class="mb-3">
            <!-- Populated via JS -->
        </div>

        <form id="formEditUser" method="POST" class="space-y-3.5">
            @csrf
            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="editUserName" required class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Username <span class="text-red-500">*</span></label>
                    <input type="text" name="username" id="editUserUsername" required class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Password Baru</label>
                    <input type="password" name="password" placeholder="Kosongkan jika tetap" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Email <span class="text-red-500">*</span></label>
                <input type="email" name="email" id="editUserEmail" required class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Peran (Role) <span class="text-red-500">*</span></label>
                <select name="role_id" id="editRoleId" required class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                    @foreach($roles as $r)
                        <option value="{{ $r->id }}" data-role="{{ $r->name }}">{{ $r->description ?: $r->name }}</option>
                    @endforeach
                </select>
            </div>

            <div id="editWilayahWrapper" class="hidden">
                <label class="block font-bold text-slate-700 uppercase mb-1">Wilayah</label>
                <select name="wilayah_id" id="editWilayahId" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                    <option value="">-- Pilih Wilayah --</option>
                    @foreach($wilayahList as $w)
                        <option value="{{ $w->id }}">{{ $w->name }}</option>
                    @endforeach
                </select>
            </div>

            <div id="editCabangWrapper" class="hidden">
                <label class="block font-bold text-slate-700 uppercase mb-1">Cabang</label>
                <select name="cabang_id" id="editCabangId" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                    <option value="">-- Pilih Cabang --</option>
                    @foreach($cabangList as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Status Akun</label>
                <select name="status" id="editStatus" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                    <option value="1">Aktif</option>
                    <option value="0">Nonaktif</option>
                </select>
            </div>

            <div class="pt-2 flex items-center justify-end gap-2">
                <button type="button" onclick="closeModal('modalEditUser')" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold transition">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold transition shadow-md">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    function handleRoleScopeVisibility(selectElem, wilayahWrap, cabangWrap) {
        const selectedOpt = selectElem.options[selectElem.selectedIndex];
        const role = selectedOpt ? selectedOpt.getAttribute('data-role') : '';

        if (role === 'admin_wilayah' || role === 'admin_wilayah_pemuda') {
            wilayahWrap.classList.remove('hidden');
            cabangWrap.classList.add('hidden');
        } else if (role === 'admin_cabang') {
            wilayahWrap.classList.add('hidden');
            cabangWrap.classList.remove('hidden');
        } else {
            wilayahWrap.classList.add('hidden');
            cabangWrap.classList.add('hidden');
        }
    }

    const addRoleId = document.getElementById('addRoleId');
    if (addRoleId) {
        addRoleId.addEventListener('change', function () {
            handleRoleScopeVisibility(this, document.getElementById('addWilayahWrapper'), document.getElementById('addCabangWrapper'));
        });
    }

    const editRoleId = document.getElementById('editRoleId');
    if (editRoleId) {
        editRoleId.addEventListener('change', function () {
            handleRoleScopeVisibility(this, document.getElementById('editWilayahWrapper'), document.getElementById('editCabangWrapper'));
        });
    }

    function openAddUserModal() {
        resetSelectedUserProfile();
        document.getElementById('formAddUser').reset();
        openModal('modalAddUser');
        setTimeout(() => {
            const input = document.getElementById('inputSearchUserUnified');
            if (input) input.focus();
        }, 150);
    }

    function clearSearchUserUnified() {
        const input = document.getElementById('inputSearchUserUnified');
        if (input) {
            input.value = '';
            input.focus();
        }
        const btnClear = document.getElementById('btnClearSearchUnified');
        if (btnClear) btnClear.classList.add('hidden');
        const box = document.getElementById('searchResultsUserUnified');
        if (box) {
            box.classList.add('hidden');
            box.innerHTML = '';
        }
    }

    let searchUnifiedTimer = null;
    function debounceSearchUserUnified(val) {
        clearTimeout(searchUnifiedTimer);
        const resultsBox = document.getElementById('searchResultsUserUnified');
        const btnClear   = document.getElementById('btnClearSearchUnified');

        if (btnClear) {
            if (val.trim().length > 0) {
                btnClear.classList.remove('hidden');
            } else {
                btnClear.classList.add('hidden');
            }
        }

        if (val.trim().length < 2) {
            resultsBox.classList.add('hidden');
            resultsBox.innerHTML = '';
            return;
        }

        resultsBox.classList.remove('hidden');
        resultsBox.innerHTML = '<div class="p-3 text-center text-slate-400"><i class="bi bi-arrow-repeat animate-spin"></i> Mencari otomatis di basis data Pemuda &amp; Warga MTA...</div>';

        searchUnifiedTimer = setTimeout(() => {
            fetch(`{{ route('admin.users.search-unified') }}?q=${encodeURIComponent(val)}`)
                .then(res => res.json())
                .then(res => {
                    const list = res.data || [];
                    if (list.length === 0) {
                        resultsBox.innerHTML = '<div class="p-3 text-center text-slate-400 italic">Tidak ditemukan data pemuda atau warga MTA yang cocok.</div>';
                        return;
                    }

                    resultsBox.innerHTML = list.map(item => {
                        const hasAccount = item.has_account;
                        const isPemuda = item.sumber_data === 'pemuda';
                        const sourceBadge = isPemuda
                            ? '<span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[9px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200"><i class="bi bi-people-fill text-[8px]"></i> Pemuda Sragen</span>'
                            : '<span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[9px] font-bold bg-sky-50 text-sky-700 border border-sky-200"><i class="bi bi-cloud-check-fill text-[8px]"></i> Warga MTA Pusat</span>';

                        return `
                            <div class="p-2.5 rounded-xl bg-white border ${hasAccount ? 'border-amber-200 bg-amber-50/20' : 'border-slate-200 hover:border-red-300'} transition flex items-center justify-between gap-3 shadow-2xs">
                                <div>
                                    <div class="font-bold text-slate-800 text-xs flex items-center gap-1.5 flex-wrap">
                                        <span>${escapeHtml(item.nama)}</span>
                                        ${sourceBadge}
                                        ${item.gender_label ? `<span class="text-[10px] text-slate-400">(${item.gender_label})</span>` : ''}
                                        ${hasAccount ? '<span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-amber-100 text-amber-800 border border-amber-200">Sudah Punya Akun</span>' : ''}
                                    </div>
                                    <div class="text-[10px] text-slate-400 mt-0.5 flex flex-wrap items-center gap-x-2 gap-y-0.5">
                                        <span>Cabang ${escapeHtml(item.cabang_name)}</span>
                                        ${item.wilayah_name && item.wilayah_name !== '-' ? `&bull; <span>${escapeHtml(item.wilayah_name)}</span>` : ''}
                                        ${item.phone ? `&bull; <span class="text-emerald-600 font-semibold">${escapeHtml(item.phone)}</span>` : ''}
                                        ${item.reg_no ? `&bull; <span class="font-mono text-slate-500">${escapeHtml(item.reg_no)}</span>` : ''}
                                    </div>
                                </div>
                                <div>
                                    ${hasAccount ? `
                                        <button type="button" disabled class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-400 text-[10px] font-bold cursor-not-allowed">
                                            Terdaftar
                                        </button>
                                    ` : `
                                        <button type="button" onclick='selectUnifiedProfile(${JSON.stringify(item)})' class="px-2.5 py-1 rounded-lg bg-red-600 hover:bg-red-700 text-white font-bold text-[10px] transition shadow-2xs flex-shrink-0 flex items-center gap-1">
                                            <i class="bi bi-check2"></i> Pilih
                                        </button>
                                    `}
                                </div>
                            </div>
                        `;
                    }).join('');
                })
                .catch(() => {
                    resultsBox.innerHTML = '<div class="p-3 text-center text-rose-500">Gagal melakukan pencarian profil.</div>';
                });
        }, 300);
    }

    function selectUnifiedProfile(item) {
        document.getElementById('addUserSumberData').value = item.sumber_data;
        document.getElementById('addUserPemudaId').value = item.id || '';
        document.getElementById('addUserMtaUuid').value = item.uuid || '';
        document.getElementById('addUserName').value = item.nama || '';

        // Display Card
        document.getElementById('selectedUserNama').textContent = item.nama;
        const isPemuda = item.sumber_data === 'pemuda';
        const badgeEl = document.getElementById('selectedUserBadge');
        const iconEl  = document.getElementById('selectedUserIconContainer');

        if (isPemuda) {
            badgeEl.className = 'inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200';
            badgeEl.innerHTML = '<i class="bi bi-people-fill text-[9px]"></i> Pemuda MTA Sragen';
            if (iconEl) iconEl.className = 'w-9 h-9 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-bold text-sm flex-shrink-0 mt-0.5';
        } else {
            badgeEl.className = 'inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-sky-100 text-sky-800 border border-sky-200';
            badgeEl.innerHTML = '<i class="bi bi-cloud-check-fill text-[9px]"></i> Warga MTA Pusat';
            if (iconEl) iconEl.className = 'w-9 h-9 rounded-xl bg-sky-600 text-white flex items-center justify-center font-bold text-sm flex-shrink-0 mt-0.5';
        }

        document.getElementById('selectedUserCabang').innerHTML = `<i class="bi bi-diagram-3"></i> Cabang ${escapeHtml(item.cabang_name)}`;
        document.getElementById('selectedUserWilayah').textContent = (item.wilayah_name && item.wilayah_name !== '-') ? `(${item.wilayah_name})` : '';
        document.getElementById('selectedUserKontak').textContent = item.phone ? `Kontak: ${item.phone}` : '';

        // Prefill form
        if (item.email && !document.getElementById('addUserEmail').value) {
            document.getElementById('addUserEmail').value = item.email;
        }
        if (!document.getElementById('addUserUsername').value) {
            const cleanName = item.nama.toLowerCase().replace(/[^a-z0-9]/g, '_').substring(0, 20);
            document.getElementById('addUserUsername').value = cleanName;
        }
        if (item.cabang_id) {
            document.getElementById('addCabangId').value = item.cabang_id;
        }
        if (item.wilayah_id) {
            document.getElementById('addWilayahId').value = item.wilayah_id;
        }

        // Switch View
        document.getElementById('sectionProfilePicker').classList.add('hidden');
        document.getElementById('selectedUserProfileCard').classList.remove('hidden');
    }

    function resetSelectedUserProfile() {
        document.getElementById('addUserSumberData').value = '';
        document.getElementById('addUserPemudaId').value = '';
        document.getElementById('addUserMtaUuid').value = '';
        document.getElementById('addUserName').value = '';

        const inputSearch = document.getElementById('inputSearchUserUnified');
        if (inputSearch) inputSearch.value = '';
        const resultsBox = document.getElementById('searchResultsUserUnified');
        if (resultsBox) {
            resultsBox.classList.add('hidden');
            resultsBox.innerHTML = '';
        }
        const btnClear = document.getElementById('btnClearSearchUnified');
        if (btnClear) btnClear.classList.add('hidden');

        document.getElementById('selectedUserProfileCard').classList.add('hidden');
        document.getElementById('sectionProfilePicker').classList.remove('hidden');
    }

    function validateUserCreationForm(e) {
        const sumberData = document.getElementById('addUserSumberData').value;
        const name = document.getElementById('addUserName').value;

        if (!sumberData || !name) {
            e.preventDefault();
            alert('Silakan cari dan pilih profil calon pengguna terlebih dahulu!');
            const input = document.getElementById('inputSearchUserUnified');
            if (input) input.focus();
            return false;
        }
        return true;
    }

    function editUser(u) {
        document.getElementById('formEditUser').action = `{{ url('admin/users/update') }}/${u.id}`;
        document.getElementById('editUserName').value = u.name;
        document.getElementById('editUserUsername').value = u.username;
        document.getElementById('editUserEmail').value = u.email;
        document.getElementById('editRoleId').value = u.role_id;
        document.getElementById('editStatus').value = u.status;
        if (u.wilayah_id) document.getElementById('editWilayahId').value = u.wilayah_id;
        if (u.cabang_id) document.getElementById('editCabangId').value = u.cabang_id;

        // Source Badge Container
        const badgeContainer = document.getElementById('editUserSourceBadgeContainer');
        if (badgeContainer) {
            if (u.sumber_data === 'pemuda' || u.pemuda_id) {
                badgeContainer.innerHTML = '<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200"><i class="bi bi-people-fill"></i> Tertaut Data Pemuda MTA Sragen</span>';
            } else if (u.sumber_data === 'warga' || u.mta_warga_uuid) {
                badgeContainer.innerHTML = '<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold bg-sky-50 text-sky-700 border border-sky-200"><i class="bi bi-cloud-check-fill"></i> Tertaut Data Warga MTA Pusat</span>';
            } else {
                badgeContainer.innerHTML = '<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200"><i class="bi bi-shield"></i> Akun Pengguna Manual / Sistem</span>';
            }
        }

        handleRoleScopeVisibility(document.getElementById('editRoleId'), document.getElementById('editWilayahWrapper'), document.getElementById('editCabangWrapper'));
        openModal('modalEditUser');
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
</script>
@endsection
