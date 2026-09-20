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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class PendataanController extends Controller
{
    public function index()
    {
        $wilayahWithCabang = Wilayah::getWithCabang();
        $cabangList        = Cabang::orderBy('name', 'ASC')->get();
        $educationLevels   = EducationLevel::orderBy('id', 'ASC')->get();
        $jobStatuses       = JobStatus::orderBy('id', 'ASC')->get();
        $skills            = Skill::orderBy('name', 'ASC')->get();
        $interests         = Interest::orderBy('name', 'ASC')->get();
        $districts         = \App\Models\District::where('regency_id', 3314)->orderBy('name', 'ASC')->get();
        $defaultOrgs       = ['SATGAS', 'BANKOM', 'SAR MTA', 'TIM PARKIR', 'ELFATA', 'TIM IKHROM'];
        $customOrgs        = Organisasi::select('organization_name')
            ->distinct()
            ->whereNotIn('organization_name', $defaultOrgs)
            ->whereNotNull('organization_name')
            ->where('organization_name', '!=', '')
            ->orderBy('organization_name', 'ASC')
            ->pluck('organization_name')
            ->toArray();

        return view('pendataan.form', [
            'wilayahList'     => $wilayahWithCabang,
            'cabangList'      => $cabangList,
            'districts'       => $districts,
            'educationLevels' => $educationLevels,
            'jobStatuses'     => $jobStatuses,
            'skills'          => $skills,
            'interests'       => $interests,
            'customOrgs'      => $customOrgs,
        ]);
    }

    public function searchNama(Request $request)
    {
        $cabangId = (int) $request->query('cabang_id', 0);
        $query    = trim((string) $request->query('q', ''));

        if ($cabangId <= 0 || mb_strlen($query) < 2) {
            return response()->json([
                'status' => 'success',
                'data'   => [],
            ]);
        }

        $cabang = Cabang::find($cabangId);
        if (!$cabang) {
            return response()->json([
                'status' => 'success',
                'data'   => [],
            ]);
        }

        // 1. Data Pemuda Lokal (khusus cabang ini)
        $cleanQuery = strtolower($query);
        $pemudaResults = Pemuda::where('cabang_id', $cabangId)
            ->where('status_data', 'active')
            ->where(function ($q) use ($query, $cleanQuery) {
                $q->where('name', 'LIKE', '%' . $query . '%')
                  ->orWhereRaw('LOWER(name) LIKE ?', ['%' . $cleanQuery . '%']);
            })
            ->orderBy('name', 'ASC')
            ->limit(10)
            ->get(['id', 'name', 'gender', 'birth_date', 'birth_place', 'mta_warga_uuid', 'registration_number']);

        $combined = [];
        $seenUuids = [];
        $seenNames = [];

        foreach ($pemudaResults as $p) {
            if (!empty($p->mta_warga_uuid)) {
                $seenUuids[$p->mta_warga_uuid] = true;
            }
            $seenNames[strtolower(trim($p->name))] = true;

            $combined[] = [
                'source'              => 'pemuda',
                'id'                  => $p->id,
                'uuid'                => $p->mta_warga_uuid,
                'name'                => $p->name,
                'gender'              => $p->gender,
                'gender_text'         => $p->gender === 'L' ? 'Laki-laki' : 'Perempuan',
                'birth_date'          => $p->birth_date ? \Carbon\Carbon::parse($p->birth_date)->format('d/m/Y') : null,
                'birth_place'         => $p->birth_place,
                'registration_number' => $p->registration_number,
                'badge'               => 'Data Pemuda',
                'badge_color'         => 'emerald',
            ];
        }

        // 2. Data Warga MTA Pusat (khusus cabang ini)
        $apiService = new \App\Services\MtaApiService();
        if ($apiService->isEnabled()) {
            try {
                // Gunakan timeout singkat (3 detik) agar jika server hosting mengalami kendala koneksi ke MTA Pusat,
                // proses autocomplete lokal tetap cepat dan tidak hanging/504 gateway timeout.
                $apiService->setTimeout(3);

                $cabangParam = $cabang->mta_uuid ?: $cabang->name;
                $wargaRes = $apiService->getWargaList([
                    'cabang'   => $cabangParam,
                    'search'   => $query,
                    'per_page' => 15,
                ]);

                if (($wargaRes['success'] ?? false) && !empty($wargaRes['data']) && is_array($wargaRes['data'])) {
                    foreach ($wargaRes['data'] as $w) {
                        $wUuid = $w['uuid'] ?? '';
                        $wName = trim($w['nama'] ?? '');
                        $wNameKey = strtolower($wName);

                        if ((!empty($wUuid) && isset($seenUuids[$wUuid])) || isset($seenNames[$wNameKey])) {
                            continue;
                        }

                        $gender = strtoupper($w['kelamin'] ?? 'L');
                        $birthText = null;
                        if (!empty($w['lahir'])) {
                            try {
                                $birthText = \Carbon\Carbon::parse($w['lahir'])->format('d/m/Y');
                            } catch (\Throwable) {}
                        } elseif (!empty($w['usia'])) {
                            $birthText = "Usia {$w['usia']} th";
                        }

                        $combined[] = [
                            'source'              => 'warga_mta',
                            'id'                  => null,
                            'uuid'                => $wUuid,
                            'name'                => $wName,
                            'gender'              => $gender,
                            'gender_text'         => $gender === 'L' ? 'Laki-laki' : 'Perempuan',
                            'birth_date'          => $birthText,
                            'birth_place'         => $w['tempat_lahir'] ?? null,
                            'phone'               => $w['nohp'] ?? null,
                            'registration_number' => null,
                            'badge'               => 'Warga MTA Pusat',
                            'badge_color'         => 'sky',
                        ];
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('[PendataanController] searchNama MTA API error: ' . $e->getMessage());
            }
        }

        return response()->json([
            'status' => 'success',
            'data'   => $combined,
        ]);
    }

    public function getWargaData(string $uuid, Request $request)
    {
        $cabangId = (int) $request->query('cabang_id', 0);
        $cabang   = Cabang::find($cabangId);

        if (!$cabang) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Cabang tidak valid.',
            ], 400);
        }

        $apiService = new \App\Services\MtaApiService();
        if (!$apiService->isEnabled()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Layanan API MTA sedang tidak aktif.',
            ], 503);
        }

        $res = $apiService->getWargaDetail($uuid);
        if (!($res['success'] ?? false) || empty($res['data'])) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Data warga tidak ditemukan di server MTA Pusat.',
            ], 404);
        }

        $w = $res['data'];

        // Verifikasi cabang: harus sesuai cabang yang dipilih
        $wCabangUuid = $w['cabang_uuid'] ?? '';
        $wCabangName = strtolower(trim($w['cabang'] ?? ''));
        $matchCabang = false;

        if (!empty($cabang->mta_uuid) && !empty($wCabangUuid) && $cabang->mta_uuid === $wCabangUuid) {
            $matchCabang = true;
        } elseif (!empty($wCabangName) && strtolower(trim($cabang->name)) === $wCabangName) {
            $matchCabang = true;
        }

        if (!$matchCabang) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Data warga tidak terdaftar pada cabang yang dipilih.',
            ], 403);
        }

        // Gunakan MtaSyncService helper untuk matching yang konsisten
        $syncService = new \App\Services\MtaSyncService($apiService);

        // Cari pencocokan kecamatan & desa Sragen jika ada
        $districtId = null;
        $villageId  = null;
        if (!empty($w['kecamatan'])) {
            $cleanKec = trim(str_replace('Kec.', '', $w['kecamatan']));
            $dist = \App\Models\District::where('regency_id', 3314)
                ->where('name', 'LIKE', '%' . $cleanKec . '%')
                ->first();
            if ($dist) {
                $districtId = $dist->id;
                if (!empty($w['desa'])) {
                    $cleanDesa = trim(str_replace(['Desa', 'Kel.', 'Kelurahan'], '', $w['desa']));
                    $vill = \App\Models\Village::where('district_id', $dist->id)
                        ->where('name', 'LIKE', '%' . $cleanDesa . '%')
                        ->first();
                    if ($vill) {
                        $villageId = $vill->id;
                    }
                }
            }
        }

        $rtrw = $syncService->parseRtRw($w['alamat_rtrw'] ?? null, $w['alamat'] ?? null);
        $marital = $syncService->matchMaritalStatus($w['menikah'] ?? '');
        $bloodType = $syncService->matchBloodType($w['goldar'] ?? '') ?: 'tidak_tahu';
        $eduLevelId = $syncService->matchEducationLevel($w['pendidikan'] ?? '');
        $jobStatusId = $syncService->matchJobStatus($w['pekerjaan'] ?? '');

        return response()->json([
            'status' => 'success',
            'data'   => [
                'mta_warga_uuid' => $w['uuid'],
                'mta_ayah_uuid'  => !empty($w['ayah_uuid']) ? $w['ayah_uuid'] : null,
                'mta_ibu_uuid'   => !empty($w['ibu_uuid']) ? $w['ibu_uuid'] : null,
                'mta_foto_url'   => (!empty($w['foto']) && !str_contains($w['foto'], 'default.png')) ? $w['foto'] : null,
                'name'           => $w['nama'],
                'gender'         => strtoupper($w['kelamin'] ?? 'L'),
                'birth_date'     => !empty($w['lahir']) ? date('Y-m-d', strtotime($w['lahir'])) : null,
                'birth_place'    => $w['tempat_lahir'] ?? 'Sragen',
                'phone'          => !empty($w['nohp']) ? preg_replace('/[^0-9+]/', '', $w['nohp']) : null,
                'email'          => !empty($w['email']) ? trim($w['email']) : null,
                'marital_status' => $marital,
                'blood_type'     => $bloodType,
                'alamat'         => [
                    'district_id'    => $districtId,
                    'village_id'     => $villageId,
                    'dusun'          => $w['desa'] ?? ($w['alamat'] ?? null),
                    'rt'             => $rtrw['rt'],
                    'rw'             => $rtrw['rw'],
                    'address_detail' => $w['alamat'] ?? null,
                ],
                'pendidikan'     => [
                    'education_level_id' => $eduLevelId,
                    'school_name'        => !empty($w['sekolah']) ? $w['sekolah'] : null,
                    'education_status'   => 'lulus',
                ],
                'pekerjaan'      => [
                    'job_status_id' => $jobStatusId,
                    'job_title'     => $w['pekerjaan'] ?? null,
                ],
                'foto'           => (!empty($w['foto']) && !str_contains($w['foto'], 'default.png')) ? $w['foto'] : null,
            ],
        ]);
    }

    public function getPemudaData(int $id, Request $request)
    {
        $cabangId = (int) $request->query('cabang_id', 0);

        $query = Pemuda::with(['alamat', 'pendidikan', 'pekerjaan', 'organisasi', 'skills', 'interests'])
            ->where('id', $id)
            ->where('status_data', 'active');

        if ($cabangId > 0) {
            $query->where('cabang_id', $cabangId);
        }

        $p = $query->first();

        if (!$p) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Data pemuda tidak ditemukan atau cabang tidak sesuai.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data'   => [
                'id'                 => $p->id,
                'cabang_id'          => $p->cabang_id,
                'name'               => $p->name,
                'gender'             => $p->gender,
                'marital_status'     => $p->marital_status,
                'blood_type'         => $p->blood_type ?: 'tidak_tahu',
                'birth_place'        => $p->birth_place,
                'birth_date'         => $p->birth_date ? \Carbon\Carbon::parse($p->birth_date)->format('Y-m-d') : null,
                'phone'              => $p->phone,
                'email'              => $p->email,
                'foto'               => $p->foto ? asset('uploads/pemuda/' . $p->foto) : null,
                'alamat'             => [
                    'district_id'    => $p->alamat?->district_id,
                    'village_id'     => $p->alamat?->village_id,
                    'dusun'          => $p->alamat?->dusun,
                    'rt'             => $p->alamat?->rt,
                    'rw'             => $p->alamat?->rw,
                    'address_detail' => $p->alamat?->address_detail,
                ],
                'pendidikan'         => [
                    'education_level_id' => $p->pendidikan?->education_level_id,
                    'school_name'        => $p->pendidikan?->school_name,
                    'major'              => $p->pendidikan?->major,
                    'education_status'   => $p->pendidikan?->education_status,
                    'graduation_year'    => $p->pendidikan?->graduation_year,
                ],
                'pekerjaan'          => [
                    'job_status_id'    => $p->pekerjaan?->job_status_id,
                    'job_title'        => $p->pekerjaan?->job_title,
                    'company_name'     => $p->pekerjaan?->company_name,
                    'business_field'   => $p->pekerjaan?->business_field,
                    'business_name'    => $p->pekerjaan?->business_name,
                    'business_address' => $p->pekerjaan?->business_address,
                    'business_contact' => $p->pekerjaan?->business_contact,
                    'business_social'  => $p->pekerjaan?->business_social,
                ],
                'organisasi'         => $p->organisasi->pluck('organization_name')->values()->toArray(),
                'skills'             => $p->skills->pluck('id')->values()->toArray(),
                'interests'          => $p->interests->pluck('id')->values()->toArray(),
            ],
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
            $existingPemuda = Pemuda::where('id', $existingId)->where('cabang_id', $cabangId)->where('status_data', 'active')->first();
        }
        if (!$existingPemuda) {
            $existingPemuda = Pemuda::findExistingPemuda($name, $gender, $birthDate, $cabangId);
        }

        $isUpdate = ($existingPemuda !== null);

        $hasUploadedFoto = $request->hasFile('foto') && $request->file('foto')->isValid();
        $hasExistingFoto = ($isUpdate && !empty($existingPemuda->foto));

        // Validasi Foto: Hanya wajib bagi pendaftaran pemuda baru laki-laki
        if (!$isUpdate && empty($request->input('mta_warga_uuid')) && $gender === 'L' && !$hasUploadedFoto) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Pas foto profil wajib diunggah untuk pendaftaran pemuda baru laki-laki.')
                ->withErrors(['foto' => 'Pas foto wajib diunggah untuk pendaftaran pemuda baru laki-laki.']);
        }

        RateLimiter::hit($throttleKey, 60);

        DB::beginTransaction();
        try {
            // Upload foto jika ada
            $fotoFilename = null;
            if ($hasUploadedFoto) {
                $fotoFile = $request->file('foto');
                $ext = strtolower($fotoFile->extension() ?: $fotoFile->getClientOriginalExtension());
                $fotoFilename = 'foto_' . date('YmdHis') . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                $fotoFile->move(public_path('uploads/pemuda'), $fotoFilename);
            } elseif ($hasExistingFoto) {
                $fotoFilename = $existingPemuda->foto;
            }

            $bloodType = $request->input('blood_type');
            if ($bloodType === '-' || $bloodType === 'tidak_tahu') {
                $bloodType = null;
            }

            $mtaWargaUuid = $request->input('mta_warga_uuid') ?: null;
            $mtaAyahUuid  = $request->input('mta_ayah_uuid') ?: null;
            $mtaIbuUuid   = $request->input('mta_ibu_uuid') ?: null;
            $mtaFotoUrl   = $request->input('mta_foto_url') ?: null;

            if ($isUpdate) {
                $pemuda = $existingPemuda;
                $hasMtaUuid = !empty($mtaWargaUuid) || !empty($pemuda->mta_warga_uuid);
                $updateData = [
                    'name'              => $name,
                    'gender'            => $gender,
                    'marital_status'    => $request->input('marital_status'),
                    'blood_type'        => $bloodType,
                    'birth_place'       => $request->input('birth_place'),
                    'birth_date'        => $birthDate,
                    'phone'             => $request->input('phone'),
                    'email'             => $request->input('email') ?: null,
                    'status_verifikasi' => $hasMtaUuid ? 'verified' : 'pending',
                ];
                if ($fotoFilename) {
                    $updateData['foto'] = $fotoFilename;
                }
                if (!empty($mtaWargaUuid)) {
                    $updateData['mta_warga_uuid'] = $mtaWargaUuid;
                    $updateData['mta_synced_at']  = now();
                    if ($mtaAyahUuid) $updateData['mta_ayah_uuid'] = $mtaAyahUuid;
                    if ($mtaIbuUuid) $updateData['mta_ibu_uuid'] = $mtaIbuUuid;
                    if ($mtaFotoUrl) $updateData['mta_foto_url'] = $mtaFotoUrl;
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
                    'status_verifikasi'   => !empty($mtaWargaUuid) ? 'verified' : 'pending',
                    'status_data'         => 'active',
                    'foto'                => $fotoFilename,
                    'mta_warga_uuid'      => $mtaWargaUuid,
                    'mta_ayah_uuid'       => $mtaAyahUuid,
                    'mta_ibu_uuid'        => $mtaIbuUuid,
                    'mta_foto_url'        => $mtaFotoUrl,
                    'mta_synced_at'       => !empty($mtaWargaUuid) ? now() : null,
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
            $orgs = (array) $request->input('organizations', []);
            $customOrg = trim((string) $request->input('custom_organization', ''));
            if (!empty($customOrg) && !in_array($customOrg, $orgs, true)) {
                $orgs[] = $customOrg;
            }
            if (!empty($orgs)) {
                $seenOrgs = [];
                foreach ($orgs as $orgName) {
                    $cleanOrg = trim((string) $orgName);
                    $cleanKey = mb_strtoupper($cleanOrg);
                    if (!empty($cleanOrg) && !isset($seenOrgs[$cleanKey])) {
                        $seenOrgs[$cleanKey] = true;
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
}
