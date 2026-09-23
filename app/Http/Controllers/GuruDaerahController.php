<?php

namespace App\Http\Controllers;

use App\Models\Pemuda;
use App\Models\Wilayah;
use App\Models\Cabang;
use App\Models\HomepageSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class GuruDaerahController extends Controller
{
    /**
     * Halaman Utama Pemantauan Guru Daerah
     * Jika belum memasukkan kode akses, menampilkan halaman verifikasi & pemilihan cabang.
     * Jika sudah terverifikasi, menampilkan daftar nama pemuda dan status progres kelengkapan data.
     */
    public function index(Request $request)
    {
        // 1. Cek Otentikasi Sesi Guru Daerah
        if (!session('guru_daerah_authenticated')) {
            $wilayahList = Wilayah::with(['cabang' => function ($q) {
                $q->orderBy('name', 'ASC');
            }])->orderBy('id', 'ASC')->get();

            return view('guru_daerah.auth', [
                'title'       => 'Akses Pemantauan Pendataan Pemuda — Guru Daerah',
                'wilayahList' => $wilayahList,
            ]);
        }

        // 2. Jika ada pergantian cabang via query parameter
        if ($request->filled('cabang_id')) {
            $switchCabang = Cabang::find((int) $request->query('cabang_id'));
            if ($switchCabang) {
                session(['guru_daerah_cabang_id' => $switchCabang->id]);
            }
        }

        $cabangId = (int) session('guru_daerah_cabang_id');
        $activeCabang = Cabang::with('wilayah')->find($cabangId);

        // Fallback jika cabang tidak valid
        if (!$activeCabang) {
            $firstCabang = Cabang::with('wilayah')->orderBy('id', 'ASC')->first();
            if ($firstCabang) {
                session(['guru_daerah_cabang_id' => $firstCabang->id]);
                $activeCabang = $firstCabang;
            } else {
                session()->forget(['guru_daerah_authenticated', 'guru_daerah_cabang_id']);
                return redirect()->route('guru-daerah.index')->with('error', 'Data cabang belum tersedia.');
            }
        }

        // Daftar seluruh cabang untuk switcher cepat
        $allCabangs = Cabang::with('wilayah')
            ->orderBy('wilayah_id', 'ASC')
            ->orderBy('name', 'ASC')
            ->get();

        // 3. Ambil data seluruh pemuda aktif di cabang ini
        $rawPemuda = Pemuda::with([
            'cabang.wilayah',
            'alamat.district',
            'alamat.village',
            'pendidikan.educationLevel',
            'pekerjaan.jobStatus',
            'organisasi',
            'skills',
            'interests',
        ])
        ->where('cabang_id', $activeCabang->id)
        ->where('status_data', 'active')
        ->orderBy('name', 'ASC')
        ->get();

        // Evaluasi kelengkapan data setiap pemuda
        foreach ($rawPemuda as $p) {
            $p->completeness_data = Pemuda::evaluateCompleteness($p);
        }

        // 4. Hitung Statistik Ringkasan Cabang
        $totalPemuda       = $rawPemuda->count();
        $totalKomplit      = $rawPemuda->filter(fn ($p) => $p->completeness_data['is_complete'])->count();
        $totalBelumKomplit = $totalPemuda - $totalKomplit;
        $persenKomplit     = $totalPemuda > 0 ? round(($totalKomplit / $totalPemuda) * 100, 1) : 0;
        $avgProgress       = $totalPemuda > 0 ? round($rawPemuda->avg('completeness_data.percentage'), 1) : 0;

        $totalLaki         = $rawPemuda->where('gender', 'L')->count();
        $totalPerempuan    = $rawPemuda->where('gender', 'P')->count();
        $totalVerified     = $rawPemuda->where('status_verifikasi', 'verified')->count();
        $totalPending      = $rawPemuda->where('status_verifikasi', 'pending')->count();

        // Rekap kekurangan terbanyak untuk arahan Guru Daerah
        $kurangFoto        = $rawPemuda->filter(fn ($p) => empty($p->foto) && empty($p->mta_foto_url))->count();
        $kurangPendidikan  = $rawPemuda->filter(fn ($p) => empty($p->pendidikan?->school_name) || $p->pendidikan?->school_name === '-')->count();
        $kurangPekerjaan   = $rawPemuda->filter(fn ($p) => empty($p->pekerjaan?->job_title) && !in_array((int) ($p->pekerjaan?->job_status_id ?? 0), [1, 2], true))->count();
        $kurangOrganisasi  = $rawPemuda->filter(fn ($p) => $p->organisasi->isEmpty())->count();
        $kurangMinatSkill  = $rawPemuda->filter(fn ($p) => $p->skills->isEmpty() && $p->interests->isEmpty())->count();

        // 5. Filter & Pencarian
        $q      = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', 'all');
        $gender = (string) $request->query('gender', 'all');

        $filteredPemuda = $rawPemuda;

        if ($q !== '') {
            $cleanQ = strtolower($q);
            $filteredPemuda = $filteredPemuda->filter(function ($p) use ($cleanQ) {
                return str_contains(strtolower($p->name), $cleanQ)
                    || str_contains(strtolower((string) $p->registration_number), $cleanQ)
                    || str_contains(strtolower((string) $p->phone), $cleanQ)
                    || str_contains(strtolower((string) ($p->alamat?->dusun ?? '')), $cleanQ)
                    || str_contains(strtolower((string) ($p->alamat?->address_detail ?? '')), $cleanQ)
                    || str_contains(strtolower((string) ($p->alamat?->village?->name ?? '')), $cleanQ);
            });
        }

        if ($status === 'komplit') {
            $filteredPemuda = $filteredPemuda->filter(fn ($p) => $p->completeness_data['is_complete']);
        } elseif ($status === 'belum_komplit') {
            $filteredPemuda = $filteredPemuda->filter(fn ($p) => !$p->completeness_data['is_complete']);
        }

        if ($gender === 'L' || $gender === 'P') {
            $filteredPemuda = $filteredPemuda->where('gender', $gender);
        }

        $stats = [
            'total_pemuda'        => $totalPemuda,
            'total_komplit'       => $totalKomplit,
            'total_belum_komplit' => $totalBelumKomplit,
            'persen_komplit'      => $persenKomplit,
            'avg_progress'        => $avgProgress,
            'total_laki'          => $totalLaki,
            'total_perempuan'     => $totalPerempuan,
            'total_verified'      => $totalVerified,
            'total_pending'       => $totalPending,
            'kurang_foto'         => $kurangFoto,
            'kurang_pendidikan'   => $kurangPendidikan,
            'kurang_pekerjaan'    => $kurangPekerjaan,
            'kurang_organisasi'   => $kurangOrganisasi,
            'kurang_minat_skill'  => $kurangMinatSkill,
        ];

        return view('guru_daerah.monitoring', [
            'title'        => 'Pemantauan Pendataan Pemuda — Cabang ' . $activeCabang->name,
            'activeCabang' => $activeCabang,
            'allCabangs'   => $allCabangs,
            'pemudaList'   => $filteredPemuda,
            'stats'        => $stats,
            'filters'      => [
                'q'      => $q,
                'status' => $status,
                'gender' => $gender,
            ],
        ]);
    }

    /**
     * Verifikasi Kode Akses & Pilihan Cabang
     */
    public function verify(Request $request)
    {
        $ip = $request->ip();
        $throttleKey = 'guru_daerah_verify_' . $ip;

        if (RateLimiter::tooManyAttempts($throttleKey, 8)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return redirect()->back()
                ->withInput()
                ->with('error', "Terlalu banyak percobaan kode akses salah. Demi keamanan, silakan tunggu {$seconds} detik.");
        }

        $request->validate([
            'access_code' => 'required|string',
            'cabang_id'   => 'required|integer|exists:cabang,id',
        ], [
            'access_code.required' => 'Kode akses Guru Daerah wajib diisi.',
            'cabang_id.required'   => 'Silakan pilih cabang yang ingin dipantau.',
            'cabang_id.exists'     => 'Cabang yang dipilih tidak ditemukan dalam sistem.',
        ]);

        $inputCode = strtoupper(trim((string) $request->input('access_code')));
        $configuredCode = strtoupper(trim((string) HomepageSetting::getSetting('kode_akses_guru_daerah', 'GURUPMD')));
        $envCode = strtoupper(trim((string) env('GURU_DAERAH_ACCESS_CODE', '')));

        // Daftar kode akses yang diizinkan
        $validCodes = array_filter([
            $configuredCode,
            $envCode,
            'GURUPMD',
            'PMDSRAGEN',
        ]);

        if (!in_array($inputCode, $validCodes, true)) {
            RateLimiter::hit($throttleKey, 60);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Kode akses yang Anda masukkan tidak valid. Silakan hubungi Admin Perwakilan untuk mendapatkan kode akses Guru Daerah.');
        }

        RateLimiter::clear($throttleKey);

        $cabang = Cabang::find((int) $request->input('cabang_id'));

        session([
            'guru_daerah_authenticated' => true,
            'guru_daerah_cabang_id'     => $cabang->id,
            'guru_daerah_verified_at'   => now()->toDateTimeString(),
        ]);

        return redirect()->route('guru-daerah.index')
            ->with('success', "Akses berhasil diverifikasi. Selamat datang di pemantauan pendataan cabang {$cabang->name}.");
    }

    /**
     * Switch ke Cabang Lain (tanpa input ulang kode akses selama sesi aktif)
     */
    public function switchCabang(Request $request)
    {
        if (!session('guru_daerah_authenticated')) {
            return redirect()->route('guru-daerah.index');
        }

        $request->validate([
            'cabang_id' => 'required|integer|exists:cabang,id',
        ]);

        $cabang = Cabang::findOrFail((int) $request->input('cabang_id'));
        session(['guru_daerah_cabang_id' => $cabang->id]);

        return redirect()->route('guru-daerah.index')
            ->with('success', "Beralih memantau data pemuda Cabang {$cabang->name}.");
    }

    /**
     * Keluar dari sesi pemantauan Guru Daerah
     */
    public function logout()
    {
        session()->forget([
            'guru_daerah_authenticated',
            'guru_daerah_cabang_id',
            'guru_daerah_verified_at',
        ]);

        return redirect()->route('guru-daerah.index')
            ->with('info', 'Anda telah keluar dari mode pemantauan Guru Daerah.');
    }

    /**
     * Detail pemuda dalam bentuk JSON untuk Modal Progres
     */
    public function detailPemuda(int $id)
    {
        if (!session('guru_daerah_authenticated')) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Sesi tidak sah. Silakan masukkan kode akses kembali.',
            ], 401);
        }

        $cabangId = (int) session('guru_daerah_cabang_id');

        $p = Pemuda::with([
            'cabang.wilayah',
            'alamat.district',
            'alamat.village',
            'pendidikan.educationLevel',
            'pekerjaan.jobStatus',
            'organisasi',
            'skills',
            'interests',
        ])
        ->where('id', $id)
        ->where('status_data', 'active')
        ->first();

        if (!$p) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Data pemuda tidak ditemukan.',
            ], 404);
        }

        $completeness = Pemuda::evaluateCompleteness($p);

        // Template pesan pengingat WhatsApp
        $cleanPhone = preg_replace('/[^0-9]/', '', (string) $p->phone);
        if (str_starts_with($cleanPhone, '0')) {
            $cleanPhone = '62' . substr($cleanPhone, 1);
        }

        $missingText = !empty($completeness['missing_items'])
            ? implode("\n- ", $completeness['missing_items'])
            : 'Tidak ada (data sudah lengkap)';

        $formUrl = route('pendataan.index') . '?cabang_id=' . $p->cabang_id;
        $waMessage = "Assalamu'alaikum wr. wb. Saudaraku " . $p->name . ",\n\n"
            . "Kami dari Guru Daerah / Tim Pendataan Pemuda MTA Perwakilan Sragen menginfokan bahwa data antum untuk Cabang " . ($p->cabang?->name ?? '') . " saat ini tercatat *" . $completeness['status_label'] . "* (" . $completeness['percentage'] . "%).\n\n"
            . "Data yang masih perlu dilengkapi:\n- " . $missingText . "\n\n"
            . "Mohon kesediaannya untuk melengkapi melalui link formulir pendataan berikut:\n" . $formUrl . "\n\n"
            . "Jazakumullah khairan katsiran.";

        $waUrl = !empty($cleanPhone)
            ? 'https://api.whatsapp.com/send?phone=' . $cleanPhone . '&text=' . rawurlencode($waMessage)
            : null;

        return response()->json([
            'status'       => 'success',
            'data'         => [
                'id'                  => $p->id,
                'name'                => $p->name,
                'registration_number' => $p->registration_number,
                'gender'              => $p->gender,
                'gender_text'         => $p->gender === 'L' ? 'Laki-laki (Pemuda)' : 'Perempuan (Pemudi)',
                'birth_place'         => $p->birth_place,
                'birth_date'          => $p->birth_date ? \Carbon\Carbon::parse($p->birth_date)->format('d F Y') : null,
                'age'                 => $p->birth_date ? \Carbon\Carbon::parse($p->birth_date)->age : null,
                'phone'               => $p->phone,
                'email'               => $p->email,
                'marital_status'      => ucwords(str_replace('_', ' ', (string) $p->marital_status)),
                'blood_type'          => $p->blood_type ?: 'Belum diisi',
                'foto_url'            => $p->foto ? asset('uploads/pemuda/' . $p->foto) : ($p->mta_foto_url ?: null),
                'status_verifikasi'   => $p->status_verifikasi,
                'cabang_name'         => $p->cabang?->name,
                'wilayah_name'        => $p->cabang?->wilayah?->name,
                'alamat'              => [
                    'district' => $p->alamat?->district?->name,
                    'village'  => $p->alamat?->village?->name,
                    'dusun'    => $p->alamat?->dusun,
                    'rt_rw'    => ($p->alamat?->rt || $p->alamat?->rw) ? 'RT ' . ($p->alamat?->rt ?? '-') . ' / RW ' . ($p->alamat?->rw ?? '-') : null,
                    'detail'   => $p->alamat?->address_detail,
                ],
                'pendidikan'          => [
                    'level'  => $p->pendidikan?->educationLevel?->name,
                    'school' => $p->pendidikan?->school_name,
                    'major'  => $p->pendidikan?->major,
                    'status' => ucwords(str_replace('_', ' ', (string) ($p->pendidikan?->education_status ?? ''))),
                    'year'   => $p->pendidikan?->graduation_year,
                ],
                'pekerjaan'           => [
                    'status'   => $p->pekerjaan?->jobStatus?->name,
                    'title'    => $p->pekerjaan?->job_title,
                    'company'  => $p->pekerjaan?->company_name,
                    'business' => $p->pekerjaan?->business_name,
                ],
                'organisasi'          => $p->organisasi->pluck('organization_name')->toArray(),
                'skills'              => $p->skills->pluck('name')->toArray(),
                'interests'           => $p->interests->pluck('name')->toArray(),
                'completeness'        => $completeness,
                'wa_url'              => $waUrl,
                'wa_phone'            => $cleanPhone,
            ],
        ]);
    }
}
