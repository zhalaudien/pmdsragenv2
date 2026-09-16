<?php

namespace App\Http\Controllers;

use App\Models\Pemuda;
use App\Models\Wilayah;
use App\Models\Cabang;
use App\Models\Alamat;
use App\Models\Pendidikan;
use App\Models\Pekerjaan;
use App\Models\Organisasi;
use App\Models\EducationLevel;
use App\Models\JobStatus;
use App\Models\Skill;
use App\Models\Interest;
use App\Models\PemudaSkill;
use App\Models\PemudaInterest;
use App\Services\MtaApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class PendataanController extends Controller
{
    protected MtaApiService $apiService;

    public function __construct()
    {
        $this->apiService = new MtaApiService();
    }

    public function index()
    {
        $wilayahWithCabang = Wilayah::getWithCabang();
        $cabangList        = Cabang::orderBy('name', 'ASC')->get();
        $educationLevels   = EducationLevel::orderBy('id', 'ASC')->get();
        $jobStatuses       = JobStatus::orderBy('id', 'ASC')->get();
        $skills            = Skill::orderBy('name', 'ASC')->get();
        $interests         = Interest::orderBy('name', 'ASC')->get();
        $districts         = \App\Models\District::where('regency_id', 3314)->orderBy('name', 'ASC')->get();

        return view('pendataan.form', [
            'wilayahList'     => $wilayahWithCabang,
            'cabangList'      => $cabangList,
            'districts'       => $districts,
            'educationLevels' => $educationLevels,
            'jobStatuses'     => $jobStatuses,
            'skills'          => $skills,
            'interests'       => $interests,
        ]);
    }

    public function simpan(Request $request)
    {
        $ip = $request->ip();
        $throttleKey = 'public_register_' . $ip;

        if (RateLimiter::tooManyAttempts($throttleKey, 10)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return redirect()->back()
                ->withInput()
                ->with('error', "Terlalu banyak permintaan pendaftaran. Demi keamanan, silakan tunggu {$seconds} detik sebelum mencoba kembali.");
        }

        $rules = [
            'cabang_id'          => 'required|integer|min:1',
            'name'               => 'required|min:3|max:150',
            'gender'             => 'required|in:L,P',
            'marital_status'     => 'required|in:belum_menikah,sudah_menikah,janda,duda',
            'blood_type'         => 'nullable|in:A,B,AB,O,tidak_tahu,-',
            'birth_place'        => 'required|max:100',
            'birth_date'         => 'required|date',
            'phone'              => 'required|min:9|max:20',
            'email'              => 'nullable|email|max:100',
            'district_id'        => 'required|integer',
            'village_id'         => 'required|integer',
            'address_detail'     => 'required|min:5',
            'education_level_id' => 'required|integer',
            'school_name'        => 'required|min:3|max:150',
            'education_status'   => 'required|in:sedang_sekolah,lulus,putus_sekolah',
            'job_status_id'      => 'required|integer',
            'business_name'      => 'nullable|max:150',
            'business_field'     => 'nullable|max:150',
            'business_address'   => 'nullable|max:500',
            'business_contact'   => 'nullable|max:50',
            'business_social'    => 'nullable|max:255',
            'foto'               => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
        ];

        $validated = $request->validate($rules);

        $name       = (string) $request->input('name');
        $gender     = (string) $request->input('gender');
        $birthDate  = (string) $request->input('birth_date');
        $cabangId   = (int) $request->input('cabang_id');
        $existingId = (int) $request->input('existing_pemuda_id', 0);

        $existingPemuda = null;
        if ($existingId > 0) {
            $existingPemuda = Pemuda::find($existingId);
        }
        if (!$existingPemuda) {
            $existingPemuda = Pemuda::findExistingPemuda($name, $gender, $birthDate, $cabangId);
        }

        $isUpdate = ($existingPemuda !== null);

        // Validasi Foto: Wajib bagi laki-laki ('L') jika belum ada foto
        $hasUploadedFoto = $request->hasFile('foto') && $request->file('foto')->isValid();
        $hasExistingFoto = ($isUpdate && !empty($existingPemuda->foto));

        if ($gender === 'L' && !$hasUploadedFoto && !$hasExistingFoto) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Pas foto profil wajib diunggah untuk pendaftar laki-laki.')
                ->withErrors(['foto' => 'Pas foto wajib diunggah untuk pendaftar laki-laki.']);
        }

        RateLimiter::hit($throttleKey, 60);

        DB::beginTransaction();
        try {
            // Upload foto jika ada
            $fotoFilename = null;
            if ($hasUploadedFoto) {
                $fotoFile = $request->file('foto');
                $fotoFilename = 'foto_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $fotoFile->getClientOriginalExtension();
                $fotoFile->move(public_path('uploads/pemuda'), $fotoFilename);
            } elseif ($hasExistingFoto) {
                $fotoFilename = $existingPemuda->foto;
            }

            $bloodType = $request->input('blood_type');
            if ($bloodType === '-' || $bloodType === 'tidak_tahu') {
                $bloodType = null;
            }

            if ($isUpdate) {
                $pemuda = $existingPemuda;
                $updateData = [
                    'name'              => $name,
                    'gender'            => $gender,
                    'marital_status'    => $request->input('marital_status'),
                    'blood_type'        => $bloodType,
                    'birth_place'       => $request->input('birth_place'),
                    'birth_date'        => $birthDate,
                    'phone'             => $request->input('phone'),
                    'email'             => $request->input('email') ?: null,
                    'status_verifikasi' => 'pending', // Perlu verifikasi ulang jika diupdate publik
                ];
                if ($fotoFilename) {
                    $updateData['foto'] = $fotoFilename;
                }
                $pemuda->update($updateData);
                $pemudaId = $pemuda->id;
            } else {
                $regNumber = Pemuda::generateRegistrationNumber($cabangId, $birthDate);

                $pemuda = Pemuda::create([
                    'cabang_id'           => $cabangId,
                    'registration_number' => $regNumber,
                    'name'                => $name,
                    'gender'              => $gender,
                    'marital_status'      => $request->input('marital_status'),
                    'blood_type'          => $bloodType,
                    'birth_place'         => $request->input('birth_place'),
                    'birth_date'          => $birthDate,
                    'phone'               => $request->input('phone'),
                    'email'               => $request->input('email') ?: null,
                    'status_verifikasi'   => 'pending',
                    'status_data'         => 'active',
                    'foto'                => $fotoFilename,
                    'mta_warga_uuid'      => $request->input('mta_warga_uuid') ?: null,
                ]);
                $pemudaId = $pemuda->id;
            }

            // 2. Alamat
            Alamat::updateOrCreate(
                ['pemuda_id' => $pemudaId],
                [
                    'province_id'    => 33, // Jawa Tengah
                    'regency_id'     => 3314, // Sragen
                    'district_id'    => (int) $request->input('district_id'),
                    'village_id'     => (int) $request->input('village_id'),
                    'dusun'          => $request->input('dusun') ?: null,
                    'rt'             => $request->input('rt') ?: null,
                    'rw'             => $request->input('rw') ?: null,
                    'address_detail' => $request->input('address_detail'),
                ]
            );

            // 3. Pendidikan
            Pendidikan::updateOrCreate(
                ['pemuda_id' => $pemudaId],
                [
                    'education_level_id' => (int) $request->input('education_level_id'),
                    'school_name'        => $request->input('school_name'),
                    'major'              => $request->input('major') ?: null,
                    'education_status'   => $request->input('education_status'),
                    'graduation_year'    => $request->input('graduation_year') ?: null,
                ]
            );

            // 4. Pekerjaan
            Pekerjaan::updateOrCreate(
                ['pemuda_id' => $pemudaId],
                [
                    'job_status_id'    => (int) $request->input('job_status_id'),
                    'job_title'        => $request->input('job_title') ?: null,
                    'company_name'     => $request->input('company_name') ?: null,
                    'business_field'   => $request->input('business_field') ?: null,
                    'business_name'    => $request->input('business_name') ?: null,
                    'business_address' => $request->input('business_address') ?: null,
                    'business_contact' => $request->input('business_contact') ?: null,
                    'business_social'  => $request->input('business_social') ?: null,
                ]
            );

            // 5. Element Dakwah / Organisasi
            Organisasi::where('pemuda_id', $pemudaId)->delete();
            $orgs = $request->input('organizations', []);
            if (is_array($orgs)) {
                foreach ($orgs as $orgName) {
                    $cleanOrg = trim((string) $orgName);
                    if (!empty($cleanOrg)) {
                        Organisasi::create([
                            'pemuda_id'         => $pemudaId,
                            'organization_name' => $cleanOrg,
                        ]);
                    }
                }
            }

            // 6. Skills (Keahlian)
            PemudaSkill::where('pemuda_id', $pemudaId)->delete();
            $skillsInput = $request->input('skills', []);
            if (is_array($skillsInput)) {
                foreach ($skillsInput as $skId) {
                    $skId = (int) $skId;
                    if ($skId > 0) {
                        PemudaSkill::create([
                            'pemuda_id' => $pemudaId,
                            'skill_id'  => $skId,
                            'level'     => 'menengah',
                        ]);
                    }
                }
            }

            // 7. Interests (Minat)
            PemudaInterest::where('pemuda_id', $pemudaId)->delete();
            $interestsInput = $request->input('interests', []);
            if (is_array($interestsInput)) {
                foreach ($interestsInput as $intId) {
                    $intId = (int) $intId;
                    if ($intId > 0) {
                        PemudaInterest::create([
                            'pemuda_id'   => $pemudaId,
                            'interest_id' => $intId,
                        ]);
                    }
                }
            }

            DB::commit();

            session()->flash('sukses_data', [
                'registration_number' => $pemuda->registration_number,
                'name'                => $pemuda->name,
                'cabang_name'         => $pemuda->cabang->name ?? '',
                'created_at'          => $pemuda->created_at->format('d/m/Y H:i'),
                'is_update'           => $isUpdate,
            ]);

            return redirect()->route('pendataan.sukses');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('[PendataanController] Simpan Error: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan sistem saat menyimpan formulir: ' . $e->getMessage());
        }
    }

    public function sukses()
    {
        $suksesData = session('sukses_data');
        if (!$suksesData) {
            return redirect()->route('pendataan.index');
        }

        return view('pendataan.sukses', ['data' => $suksesData]);
    }

    public function searchWarga(Request $request)
    {
        $q = trim((string) $request->input('q'));
        if (mb_strlen($q) < 2) {
            return response()->json(['success' => false, 'message' => 'Kata kunci minimal 2 karakter.', 'data' => []]);
        }

        $res = $this->apiService->searchWarga($q, [
            'cabang_uuid' => $request->input('cabang_uuid'),
            'kelamin'     => $request->input('kelamin'),
            'limit'       => (int) ($request->input('limit', 15)),
        ]);

        return response()->json($res);
    }

    public function wargaDetail(string $uuid)
    {
        $res = $this->apiService->getWargaDetail($uuid);
        return response()->json($res);
    }

    public function pemudaDetail(int $id)
    {
        $pemuda = Pemuda::getPemudaDetail($id);
        if (!$pemuda) {
            return response()->json(['status' => 'error', 'message' => 'Data tidak ditemukan.'], 404);
        }
        return response()->json(['status' => 'success', 'data' => $pemuda]);
    }

    public function checkData(Request $request)
    {
        $name      = trim((string) $request->input('name'));
        $gender    = trim((string) $request->input('gender'));
        $birthDate = trim((string) $request->input('birth_date'));
        $cabangId  = (int) $request->input('cabang_id');
        $excludeId = $request->input('exclude_id') ? (int) $request->input('exclude_id') : null;

        if (empty($name) || empty($birthDate) || $cabangId <= 0) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Parameter nama, tanggal lahir, dan cabang wajib diisi.',
            ]);
        }

        $existing = Pemuda::with(['alamat', 'pendidikan', 'pekerjaan', 'organisasi', 'skills', 'interests'])
            ->where('cabang_id', $cabangId)
            ->where('birth_date', date('Y-m-d', strtotime($birthDate)))
            ->whereRaw('LOWER(TRIM(name)) = ?', [strtolower($name)]);

        if (!empty($gender) && in_array($gender, ['L', 'P'], true)) {
            $existing->where('gender', $gender);
        }
        if ($excludeId) {
            $existing->where('id', '!=', $excludeId);
        }

        $found = $existing->first();

        if ($found) {
            return response()->json([
                'status' => 'duplicate',
                'found'  => true,
                'data'   => [
                    'id'                  => $found->id,
                    'registration_number' => $found->registration_number,
                    'name'                => $found->name,
                    'gender'              => $found->gender,
                    'birth_date'          => $found->birth_date ? $found->birth_date->format('Y-m-d') : '',
                    'birth_place'         => $found->birth_place,
                    'marital_status'      => $found->marital_status,
                    'blood_type'          => $found->blood_type,
                    'phone'               => $found->phone,
                    'email'               => $found->email,
                    'district_id'         => $found->alamat->district_id ?? null,
                    'village_id'          => $found->alamat->village_id ?? null,
                    'dusun'               => $found->alamat->dusun ?? '',
                    'rt'                  => $found->alamat->rt ?? '',
                    'rw'                  => $found->alamat->rw ?? '',
                    'address_detail'      => $found->alamat->address_detail ?? '',
                    'education_level_id'  => $found->pendidikan->education_level_id ?? null,
                    'school_name'         => $found->pendidikan->school_name ?? '',
                    'major'               => $found->pendidikan->major ?? '',
                    'education_status'    => $found->pendidikan->education_status ?? 'lulus',
                    'graduation_year'     => $found->pendidikan->graduation_year ?? '',
                    'job_status_id'       => $found->pekerjaan->job_status_id ?? null,
                    'job_title'           => $found->pekerjaan->job_title ?? '',
                    'company_name'        => $found->pekerjaan->company_name ?? '',
                    'business_name'       => $found->pekerjaan->business_name ?? '',
                    'business_field'      => $found->pekerjaan->business_field ?? '',
                    'organizations'       => $found->organisasi->pluck('organization_name')->toArray(),
                    'skills'              => $found->skills->pluck('id')->toArray(),
                    'interests'           => $found->interests->pluck('id')->toArray(),
                ],
                'csrfHash' => csrf_token(),
                'message' => 'Data Anda telah ditemukan di cabang ini. Anda dapat memperbarui data jika diperlukan.',
            ]);
        }

        return response()->json([
            'status'   => 'unique',
            'found'    => false,
            'csrfHash' => csrf_token(),
            'message'  => 'Data belum terdaftar di cabang ini.',
        ]);
    }
}
