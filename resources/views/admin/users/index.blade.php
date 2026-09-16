@extends('admin.layouts.main')

@section('title', 'Manajemen Pengguna & Admin')

@section('content')

<!-- HEADER & ACTIONS -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Manajemen Pengguna &amp; Akun</h2>
        <p class="text-xs text-slate-500 mt-0.5">Kelola akun administrator sistem, hak akses tingkat cabang, wilayah, pemuda/pemudi, dan superadmin.</p>
    </div>

    <button type="button" onclick="openModal('modalAddUser')" class="px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold text-xs transition shadow-md flex items-center gap-2 self-start sm:self-auto">
        <i class="bi bi-person-plus-fill"></i>
        <span>Tambah Pengguna Baru</span>
    </button>
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
                    <th class="py-3 px-4">Nama Pengguna</th>
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

<!-- MODAL TAMBAH USER -->
<div id="modalAddUser" data-modal class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 animate-in fade-in zoom-in-95 text-xs">
        <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <i class="bi bi-person-plus text-red-600"></i>
                <span>Tambah Pengguna Baru</span>
            </h3>
            <button type="button" onclick="closeModal('modalAddUser')" class="text-slate-400 hover:text-slate-600">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form action="{{ route('admin.users.simpan') }}" method="POST" class="space-y-3.5">
            @csrf
            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                <input type="text" name="name" required placeholder="Nama Administrator" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Username <span class="text-red-500">*</span></label>
                    <input type="text" name="username" required placeholder="username_unik" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Password <span class="text-red-500">*</span></label>
                    <input type="password" name="password" required placeholder="Minimal 6 karakter" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Email <span class="text-red-500">*</span></label>
                <input type="email" name="email" required placeholder="admin@pmdsragen.id" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Peran (Role) <span class="text-red-500">*</span></label>
                <select name="role_id" id="addRoleId" required class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                    <option value="">-- Pilih Peran --</option>
                    @foreach($roles as $r)
                        <option value="{{ $r->id }}" data-role="{{ $r->name }}">{{ $r->description ?: $r->name }}</option>
                    @endforeach
                </select>
            </div>

            <div id="addWilayahWrapper" class="hidden">
                <label class="block font-bold text-slate-700 uppercase mb-1">Wilayah</label>
                <select name="wilayah_id" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                    <option value="">-- Pilih Wilayah --</option>
                    @foreach($wilayahList as $w)
                        <option value="{{ $w->id }}">{{ $w->name }}</option>
                    @endforeach
                </select>
            </div>

            <div id="addCabangWrapper" class="hidden">
                <label class="block font-bold text-slate-700 uppercase mb-1">Cabang</label>
                <select name="cabang_id" class="w-full py-2.5 px-3 rounded-xl border border-slate-300 bg-slate-50 focus:ring-red-500 focus:border-red-500">
                    <option value="">-- Pilih Cabang --</option>
                    @foreach($cabangList as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="pt-2 flex items-center justify-end gap-2">
                <button type="button" onclick="closeModal('modalAddUser')" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold transition">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold transition shadow-md">Simpan Pengguna</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDIT USER -->
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

    function editUser(u) {
        document.getElementById('formEditUser').action = `{{ url('admin/users/update') }}/${u.id}`;
        document.getElementById('editUserName').value = u.name;
        document.getElementById('editUserUsername').value = u.username;
        document.getElementById('editUserEmail').value = u.email;
        document.getElementById('editRoleId').value = u.role_id;
        document.getElementById('editStatus').value = u.status;
        if (u.wilayah_id) document.getElementById('editWilayahId').value = u.wilayah_id;
        if (u.cabang_id) document.getElementById('editCabangId').value = u.cabang_id;

        handleRoleScopeVisibility(document.getElementById('editRoleId'), document.getElementById('editWilayahWrapper'), document.getElementById('editCabangWrapper'));
        openModal('modalEditUser');
    }
</script>
@endsection
