<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Biodata Pemuda' }}</title>
    <link rel="shortcut icon" href="{{ asset('icons/pemudamta.png') }}" type="image/png">
    
    @vite(['resources/css/app.css'])

    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; padding: 0 !important; }
            .page-container { border: none !important; box-shadow: none !important; padding: 0 !important; width: 100% !important; max-width: 100% !important; }
        }
        .table-cetak td {
            padding: 4px 6px;
            vertical-align: top;
            font-size: 11px;
        }
        .section-header {
            border-bottom: 1.5px solid #0f172a;
            font-weight: 800;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.05em;
            margin-top: 14px;
            margin-bottom: 6px;
            padding-bottom: 2px;
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 font-sans p-4 sm:p-8">

    <!-- Top Action Bar -->
    <div class="no-print max-w-4xl mx-auto mb-4 flex items-center justify-between p-3 rounded-2xl bg-white border border-slate-200 shadow-sm text-xs font-semibold">
        <div class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
            <span>Mode Cetak Dokumen Resmi &bull; Gunakan kertas ukuran A4</span>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="px-4 py-1.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold transition shadow-sm">
                Cetak Sekarang
            </button>
            <button onclick="window.close()" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 transition">
                Tutup
            </button>
        </div>
    </div>

    <!-- DOCUMENT SHEET -->
    <div class="page-container max-w-4xl mx-auto bg-white p-8 sm:p-12 rounded-3xl shadow-lg border border-slate-200">
        <!-- HEADER KOP SURAT -->
        <div class="flex items-center justify-center gap-4 pb-4 border-b-2 border-slate-900 text-center">
            <img src="{{ asset('icons/pemudamta.png') }}" alt="Logo" class="w-16 h-18 object-contain">
            <div>
                <h3 class="text-sm font-black uppercase tracking-wider text-slate-900">MAJLIS TAFSIR AL-QUR'AN (MTA)</h3>
                <h4 class="text-base font-black uppercase text-red-700">PENGURUS PEMUDA MTA PERWAKILAN SRAGEN</h4>
                <p class="text-[10px] text-slate-600 mt-0.5">Sekretariat Perwakilan Kabupaten Sragen &bull; Formulir Data Induk Pemuda</p>
            </div>
        </div>

        <!-- FORM TITLE & FOTO -->
        <div class="flex items-start justify-between gap-4 my-4">
            <div>
                <h5 class="text-sm font-bold text-slate-900 uppercase">FORMULIR BIODATA PEMUDA</h5>
                <div class="text-xs mt-1">Nomor Registrasi: <strong class="font-mono text-red-700">{{ $pemuda->registration_number }}</strong></div>
                <div class="text-[10px] text-slate-500 mt-0.5">
                    Status: <span class="font-bold {{ $pemuda->status_verifikasi === 'verified' ? 'text-emerald-700' : 'text-amber-700' }}">{{ $pemuda->status_verifikasi === 'verified' ? 'TERVERIFIKASI PUSAT' : 'BELUM TERVERIFIKASI' }}</span>
                    &bull; Terdaftar: {{ $pemuda->created_at ? $pemuda->created_at->format('d/m/Y H:i') : '-' }} WIB
                </div>
            </div>
            <div class="w-20 h-24 sm:w-24 sm:h-30 rounded-xl border border-slate-300 overflow-hidden bg-slate-50 flex items-center justify-center text-slate-400 text-[10px] flex-shrink-0">
                @if(!empty($pemuda->foto) && file_exists(public_path('uploads/pemuda/' . $pemuda->foto)))
                    <img src="{{ asset('uploads/pemuda/' . $pemuda->foto) }}" class="w-full h-full object-cover">
                @elseif(!empty($pemuda->mta_foto_url))
                    <img src="{{ $pemuda->mta_foto_url }}" class="w-full h-full object-cover">
                @else
                    Foto 3x4
                @endif
            </div>
        </div>

        <!-- I. DATA PRIBADI -->
        <div class="section-header">I. DATA PRIBADI</div>
        <table class="w-full table-cetak">
            <tr>
                <td class="w-1/4 text-slate-600">Nama Lengkap</td>
                <td class="w-4">:</td>
                <td class="font-bold text-slate-900">{{ $pemuda->name }}</td>
            </tr>
            <tr>
                <td class="text-slate-600">NIK</td>
                <td>:</td>
                <td class="font-mono">{{ $pemuda->nik ?? '-' }}</td>
            </tr>
            <tr>
                <td class="text-slate-600">Jenis Kelamin</td>
                <td>:</td>
                <td>{{ $pemuda->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
            </tr>
            <tr>
                <td class="text-slate-600">Status Pernikahan</td>
                <td>:</td>
                <td>{{ ucfirst(str_replace('_', ' ', $pemuda->marital_status ?? '-')) }}</td>
            </tr>
            <tr>
                <td class="text-slate-600">Golongan Darah</td>
                <td>:</td>
                <td class="font-bold">{{ $pemuda->blood_type ?? '-' }}</td>
            </tr>
            <tr>
                <td class="text-slate-600">Tempat, Tanggal Lahir</td>
                <td>:</td>
                <td>{{ $pemuda->birth_place ?? '-' }}, {{ $pemuda->birth_date ? \Carbon\Carbon::parse($pemuda->birth_date)->format('d F Y') : '-' }}</td>
            </tr>
            <tr>
                <td class="text-slate-600">No. WhatsApp / HP</td>
                <td>:</td>
                <td>{{ $pemuda->phone ?? '-' }}</td>
            </tr>
            <tr>
                <td class="text-slate-600">Email</td>
                <td>:</td>
                <td>{{ $pemuda->email ?? '-' }}</td>
            </tr>
        </table>

        <!-- II. WILAYAH & ALAMAT -->
        <div class="section-header">II. DATA KEANGGOTAAN &amp; ALAMAT DOMISILI</div>
        <table class="w-full table-cetak">
            <tr>
                <td class="w-1/4 text-slate-600">Cabang Binaan</td>
                <td class="w-4">:</td>
                <td class="font-bold">{{ $pemuda->cabang->name ?? '-' }} ({{ $pemuda->cabang->wilayah->name ?? '-' }})</td>
            </tr>
            <tr>
                <td class="text-slate-600">Kecamatan</td>
                <td>:</td>
                <td>{{ $pemuda->alamat->district->name ?? '-' }}</td>
            </tr>
            <tr>
                <td class="text-slate-600">Desa / Kelurahan</td>
                <td>:</td>
                <td>{{ $pemuda->alamat->village->name ?? '-' }}</td>
            </tr>
            <tr>
                <td class="text-slate-600">Dusun &amp; RT / RW</td>
                <td>:</td>
                <td>{{ $pemuda->alamat->dusun ?? '-' }}, RT {{ $pemuda->alamat->rt ?? '-' }} / RW {{ $pemuda->alamat->rw ?? '-' }}</td>
            </tr>
            <tr>
                <td class="text-slate-600">Alamat Lengkap</td>
                <td>:</td>
                <td>{{ $pemuda->alamat->address_detail ?? '-' }}</td>
            </tr>
        </table>

        <!-- III. PENDIDIKAN & PEKERJAAN -->
        <div class="section-header">III. PENDIDIKAN &amp; PEKERJAAN</div>
        <table class="w-full table-cetak">
            <tr>
                <td class="w-1/4 text-slate-600">Jenjang Pendidikan</td>
                <td class="w-4">:</td>
                <td>{{ $pemuda->pendidikan->educationLevel->name ?? '-' }}</td>
            </tr>
            <tr>
                <td class="text-slate-600">Nama Lembaga / Sekolah</td>
                <td>:</td>
                <td class="font-semibold">{{ $pemuda->pendidikan->school_name ?? '-' }}</td>
            </tr>
            <tr>
                <td class="text-slate-600">Jurusan / Status</td>
                <td>:</td>
                <td>{{ $pemuda->pendidikan->major ?? '-' }} ({{ $pemuda->pendidikan->education_status ?? '-' }})</td>
            </tr>
            <tr>
                <td class="text-slate-600">Status Pekerjaan</td>
                <td>:</td>
                <td class="font-semibold">{{ $pemuda->pekerjaan->jobStatus->name ?? '-' }}</td>
            </tr>
            <tr>
                <td class="text-slate-600">Jabatan / Profesi</td>
                <td>:</td>
                <td>{{ $pemuda->pekerjaan->job_title ?? '-' }} di {{ $pemuda->pekerjaan->company_name ?? '-' }}</td>
            </tr>
            @if(!empty($pemuda->pekerjaan->business_field))
                <tr>
                    <td class="text-slate-600">Bidang Usaha Mandiri</td>
                    <td>:</td>
                    <td>{{ $pemuda->pekerjaan->business_field }}</td>
                </tr>
            @endif
        </table>

        <!-- IV. ORGANISASI & KEAHLIAN -->
        <div class="section-header">IV. ELEMENT DAKWAH &amp; POTENSI DIRI</div>
        <table class="w-full table-cetak">
            <tr>
                <td class="w-1/4 text-slate-600">Element Dakwah Diikuti</td>
                <td class="w-4">:</td>
                <td>
                    @php $orgs = $pemuda->organisasi ? $pemuda->organisasi->pluck('organization_name')->toArray() : []; @endphp
                    {{ !empty($orgs) ? implode(', ', $orgs) : '-' }}
                </td>
            </tr>
            <tr>
                <td class="text-slate-600">Bakat / Keahlian</td>
                <td>:</td>
                <td>
                    @php $sks = $pemuda->skills ? $pemuda->skills->pluck('name')->toArray() : []; @endphp
                    {{ !empty($sks) ? implode(', ', $sks) : '-' }}
                </td>
            </tr>
            <tr>
                <td class="text-slate-600">Minat / Ketertarikan</td>
                <td>:</td>
                <td>
                    @php $ints = $pemuda->interests ? $pemuda->interests->pluck('name')->toArray() : []; @endphp
                    {{ !empty($ints) ? implode(', ', $ints) : '-' }}
                </td>
            </tr>
        </table>

        <!-- TANDA TANGAN -->
        <div class="mt-8 pt-4 grid grid-cols-2 text-center text-xs">
            <div>
                <p class="text-slate-500 mb-12">Mengetahui,<br>Pengurus Cabang</p>
                <p class="font-bold border-t border-slate-300 mx-auto w-40 pt-1 text-slate-900">( ........................................ )</p>
            </div>
            <div>
                <p class="text-slate-500 mb-12">Sragen, {{ date('d F Y') }}<br>Pemuda Yang Bersangkutan</p>
                <p class="font-bold border-t border-slate-300 mx-auto w-40 pt-1 text-slate-900">{{ $pemuda->name }}</p>
            </div>
        </div>
    </div>
</body>
</html>
