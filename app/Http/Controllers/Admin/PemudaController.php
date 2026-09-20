<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
use App\Models\District;
use App\Models\Village;
use App\Services\PemudaImportService;
use App\Services\PemudaExportService;
use App\Services\PemudaBackupService;
use App\Services\MtaSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PemudaController extends Controller
{
    protected function getScope(): array
    {
        $user = auth()->user();
        return [
            'role'       => $user?->role?->name ?? session('role'),
            'wilayah_id' => $user?->wilayah_id ?? session('wilayah_id'),
            'cabang_id'  => $user?->cabang_id ?? session('cabang_id'),
        ];
    }

    public function index(Request $request)
    {
        $scope = $this->getScope();

        $filters = [
            'search'             => $request->input('search'),
            'wilayah_id'         => $request->input('wilayah_id'),
            'cabang_id'          => $request->input('cabang_id'),
            'gender'             => $request->input('gender'),
            'marital_status'     => $request->input('marital_status'),
            'blood_type'         => $request->input('blood_type'),
            'status_verifikasi'  => $request->input('status_verifikasi'),
            'status_data'        => $request->input('status_data', 'active'),
            'education_level_id' => $request->input('education_level_id'),
            'job_status_id'      => $request->input('job_status_id'),
            'start_date'         => $request->input('start_date'),
            'end_date'           => $request->input('end_date'),
        ];

        if (in_array($scope['role'], ['admin_wilayah', 'admin_wilayah_pemuda'], true)) {
            $filters['wilayah_id'] = $scope['wilayah_id'];
        } elseif ($scope['role'] === 'admin_cabang') {
            $filters['wilayah_id'] = $scope['wilayah_id'];
            $filters['cabang_id']  = $scope['cabang_id'];
        }

        if (in_array($scope['role'], ['admin_wilayah_pemuda', 'admin_pemuda'], true)) {
            $filters['gender'] = 'L';
        } elseif ($scope['role'] === 'admin_pemudi') {
            $filters['gender'] = 'P';
        }

        if ($filters['status_data'] === 'all') {
            unset($filters['status_data']);
        }

        $perPage = (int) ($request->input('per_page', 15));
        if ($perPage < 5 || $perPage > 100) {
            $perPage = 15;
        }

        $query = Pemuda::with(['cabang.wilayah', 'alamat.village', 'pendidikan.educationLevel', 'pekerjaan.jobStatus'])
            ->filtered($filters, $scope)
            ->orderBy('created_at', 'DESC');

        $pemudaList = $query->paginate($perPage)->withQueryString();
        $summary    = Pemuda::getCountsSummary($scope);

        $wilayahQuery = Wilayah::orderBy('id', 'ASC');
        if (in_array($scope['role'], ['admin_wilayah', 'admin_wilayah_pemuda', 'admin_cabang'], true) && !empty($scope['wilayah_id'])) {
            $wilayahQuery->where('id', (int) $scope['wilayah_id']);
        }
        $wilayahList = $wilayahQuery->get();

        $cabangQuery = Cabang::orderBy('name', 'ASC');
        if ($scope['role'] === 'admin_cabang' && !empty($scope['cabang_id'])) {
            $cabangQuery->where('id', (int) $scope['cabang_id']);
        } elseif (in_array($scope['role'], ['admin_wilayah', 'admin_wilayah_pemuda'], true) && !empty($scope['wilayah_id'])) {
            $cabangQuery->where('wilayah_id', (int) $scope['wilayah_id']);
        } elseif (!empty($filters['wilayah_id'])) {
            $cabangQuery->where('wilayah_id', (int) $filters['wilayah_id']);
        }
        $cabangList = $cabangQuery->get();

        return view('admin.pemuda.index', [
            'title'           => 'Manajemen Data Pemuda',
            'pemudaList'      => $pemudaList,
            'filters'         => $filters,
            'perPage'         => $perPage,
            'summary'         => $summary,
            'wilayahList'     => $wilayahList,
            'cabangList'      => $cabangList,
            'educationLevels' => EducationLevel::all(),
            'jobStatuses'     => JobStatus::all(),
            'user'            => session()->all(),
        ]);
    }

    public function detail(int $id)
    {
        $scope  = $this->getScope();
        $pemuda = Pemuda::getPemudaDetail($id, $scope);

        if (!$pemuda) {
            return redirect()->route('admin.pemuda.index')
                ->with('error', 'Data pemuda tidak ditemukan atau Anda tidak memiliki akses ke data tersebut.');
        }

        return view('admin.pemuda.detail', [
            'title'  => 'Detail Pemuda: ' . $pemuda->name,
            'pemuda' => $pemuda,
            'user'   => session()->all(),
        ]);
    }

    public function tambah()
    {
        $scope = $this->getScope();

        $wilayahQuery = Wilayah::orderBy('id', 'ASC');
        if (in_array($scope['role'], ['admin_wilayah', 'admin_wilayah_pemuda', 'admin_cabang'], true) && !empty($scope['wilayah_id'])) {
            $wilayahQuery->where('id', (int) $scope['wilayah_id']);
        }
        $wilayahList = $wilayahQuery->get();

        $cabangQuery = Cabang::orderBy('name', 'ASC');
        if ($scope['role'] === 'admin_cabang' && !empty($scope['cabang_id'])) {
            $cabangQuery->where('id', (int) $scope['cabang_id']);
        } elseif (in_array($scope['role'], ['admin_wilayah', 'admin_wilayah_pemuda'], true) && !empty($scope['wilayah_id'])) {
            $cabangQuery->where('wilayah_id', (int) $scope['wilayah_id']);
        }
        $cabangList = $cabangQuery->get();

        $defaultOrgs = ['SATGAS', 'BANKOM', 'SAR MTA', 'TIM PARKIR', 'ELFATA', 'TIM IKHROM'];
        $customOrgs  = Organisasi::select('organization_name')
            ->distinct()
            ->whereNotIn('organization_name', $defaultOrgs)
            ->whereNotNull('organization_name')
            ->where('organization_name', '!=', '')
            ->orderBy('organization_name', 'ASC')
            ->pluck('organization_name')
            ->toArray();

        return view('admin.pemuda.form', [
            'title'           => 'Tambah Data Pemuda',
            'isEdit'          => false,
            'pemuda'          => null,
            'wilayahList'     => $wilayahList,
            'cabangList'      => $cabangList,
            'districts'       => District::where('regency_id', 3314)->orderBy('name', 'ASC')->get(),
            'educationLevels' => EducationLevel::orderBy('id', 'ASC')->get(),
            'jobStatuses'     => JobStatus::orderBy('id', 'ASC')->get(),
            'skills'          => Skill::orderBy('name', 'ASC')->get(),
            'interests'       => Interest::orderBy('name', 'ASC')->get(),
            'customOrgs'      => $customOrgs,
            'user'            => session()->all(),
            'scope'           => $scope,
        ]);
    }

    public function simpan(Request $request)
    {
        $scope = $this->getScope();

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
            'foto'               => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
        ];

        $request->validate($rules);

        $name       = trim((string) $request->input('name'));
        $gender     = (string) $request->input('gender');
        $birthDate  = (string) $request->input('birth_date');
        $cabangId   = (int) $request->input('cabang_id');

        // Check scope access
        if ($scope['role'] === 'admin_cabang' && (int) $scope['cabang_id'] !== $cabangId) {
            abort(403, 'Anda hanya dapat menambahkan data pada cabang Anda sendiri.');
        }

        DB::beginTransaction();
        try {
            $fotoFilename = null;
            if ($request->hasFile('foto') && $request->file('foto')->isValid()) {
                $fotoFile = $request->file('foto');
                $ext = strtolower($fotoFile->extension() ?: $fotoFile->getClientOriginalExtension());
                $fotoFilename = 'foto_' . date('YmdHis') . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                $fotoFile->move(public_path('uploads/pemuda'), $fotoFilename);
            }

            $bloodType = $request->input('blood_type');
            if ($bloodType === '-' || $bloodType === 'tidak_tahu') {
                $bloodType = null;
            }

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
                'created_by'          => auth()->id(),
            ]);

            Alamat::create([
                'pemuda_id'      => $pemuda->id,
                'province_id'    => 33,
                'regency_id'     => 3314,
                'district_id'    => (int) $request->input('district_id'),
                'village_id'     => (int) $request->input('village_id'),
                'dusun'          => $request->input('dusun') ?: null,
                'rt'             => $request->input('rt') ?: null,
                'rw'             => $request->input('rw') ?: null,
                'address_detail' => $request->input('address_detail'),
            ]);

            Pendidikan::create([
                'pemuda_id'          => $pemuda->id,
                'education_level_id' => (int) $request->input('education_level_id'),
                'school_name'        => $request->input('school_name'),
                'major'              => $request->input('major') ?: null,
                'education_status'   => $request->input('education_status'),
                'graduation_year'    => $request->input('graduation_year') ?: null,
            ]);

            Pekerjaan::create([
                'pemuda_id'        => $pemuda->id,
                'job_status_id'    => (int) $request->input('job_status_id'),
                'job_title'        => $request->input('job_title') ?: null,
                'company_name'     => $request->input('company_name') ?: null,
                'business_field'   => $request->input('business_field') ?: null,
                'business_name'    => $request->input('business_name') ?: null,
                'business_address' => $request->input('business_address') ?: null,
                'business_contact' => $request->input('business_contact') ?: null,
                'business_social'  => $request->input('business_social') ?: null,
            ]);

            Organisasi::where('pemuda_id', $pemuda->id)->delete();
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
                            'pemuda_id'         => $pemuda->id,
                            'organization_name' => $cleanOrg,
                        ]);
                    }
                }
            }

            $skillsInput = $request->input('skills', []);
            $customSkillsInput = $request->input('custom_skills', []);
            $selectedSkillIds = [];
            $customSkillNames = [];

            if (is_array($skillsInput)) {
                foreach ($skillsInput as $item) {
                    if (is_numeric($item)) {
                        $skId = (int) $item;
                        if ($skId > 0) $selectedSkillIds[] = $skId;
                    } else {
                        $cleanName = trim((string) $item);
                        if ($cleanName !== '') $customSkillNames[] = $cleanName;
                    }
                }
            }
            if (!empty($customSkillsInput)) {
                $rawList = is_array($customSkillsInput) ? $customSkillsInput : explode(',', (string) $customSkillsInput);
                foreach ($rawList as $rawName) {
                    $cleanName = trim((string) $rawName);
                    if ($cleanName !== '') $customSkillNames[] = $cleanName;
                }
            }
            foreach ($customSkillNames as $name) {
                $cleanName = trim($name);
                if ($cleanName === '') continue;
                $existingSkill = Skill::where('name', $cleanName)
                    ->orWhereRaw('LOWER(name) = ?', [mb_strtolower($cleanName)])
                    ->first();
                if (!$existingSkill) {
                    $existingSkill = Skill::create(['name' => ucwords($cleanName)]);
                }
                if ($existingSkill && !in_array($existingSkill->id, $selectedSkillIds, true)) {
                    $selectedSkillIds[] = $existingSkill->id;
                }
            }
            foreach (array_unique($selectedSkillIds) as $skId) {
                PemudaSkill::create([
                    'pemuda_id' => $pemuda->id,
                    'skill_id'  => $skId,
                    'level'     => 'menengah',
                ]);
            }

            $interestsInput = $request->input('interests', []);
            $customInterestsInput = $request->input('custom_interests', []);
            $selectedInterestIds = [];
            $customInterestNames = [];

            if (is_array($interestsInput)) {
                foreach ($interestsInput as $item) {
                    if (is_numeric($item)) {
                        $intId = (int) $item;
                        if ($intId > 0) $selectedInterestIds[] = $intId;
                    } else {
                        $cleanName = trim((string) $item);
                        if ($cleanName !== '') $customInterestNames[] = $cleanName;
                    }
                }
            }
            if (!empty($customInterestsInput)) {
                $rawList = is_array($customInterestsInput) ? $customInterestsInput : explode(',', (string) $customInterestsInput);
                foreach ($rawList as $rawName) {
                    $cleanName = trim((string) $rawName);
                    if ($cleanName !== '') $customInterestNames[] = $cleanName;
                }
            }
            foreach ($customInterestNames as $name) {
                $cleanName = trim($name);
                if ($cleanName === '') continue;
                $existingInt = Interest::where('name', $cleanName)
                    ->orWhereRaw('LOWER(name) = ?', [mb_strtolower($cleanName)])
                    ->first();
                if (!$existingInt) {
                    $existingInt = Interest::create(['name' => ucwords($cleanName)]);
                }
                if ($existingInt && !in_array($existingInt->id, $selectedInterestIds, true)) {
                    $selectedInterestIds[] = $existingInt->id;
                }
            }
            foreach (array_unique($selectedInterestIds) as $intId) {
                PemudaInterest::create([
                    'pemuda_id'   => $pemuda->id,
                    'interest_id' => $intId,
                ]);
            }

            DB::commit();

            return redirect()->route('admin.pemuda.detail', $pemuda->id)
                ->with('success', 'Data pemuda berhasil ditambahkan.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('[PemudaController] Simpan Error: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    public function edit(int $id)
    {
        $scope  = $this->getScope();
        $pemuda = Pemuda::getPemudaDetail($id, $scope);

        if (!$pemuda) {
            return redirect()->route('admin.pemuda.index')
                ->with('error', 'Data pemuda tidak ditemukan atau Anda tidak memiliki akses ke data tersebut.');
        }

        $wilayahQuery = Wilayah::orderBy('id', 'ASC');
        if (in_array($scope['role'], ['admin_wilayah', 'admin_wilayah_pemuda', 'admin_cabang'], true) && !empty($scope['wilayah_id'])) {
            $wilayahQuery->where('id', (int) $scope['wilayah_id']);
        }
        $wilayahList = $wilayahQuery->get();

        $cabangQuery = Cabang::orderBy('name', 'ASC');
        if ($scope['role'] === 'admin_cabang' && !empty($scope['cabang_id'])) {
            $cabangQuery->where('id', (int) $scope['cabang_id']);
        } elseif (in_array($scope['role'], ['admin_wilayah', 'admin_wilayah_pemuda'], true) && !empty($scope['wilayah_id'])) {
            $cabangQuery->where('wilayah_id', (int) $scope['wilayah_id']);
        }
        $cabangList = $cabangQuery->get();

        $selectedDistrictId = $pemuda->alamat->district_id ?? 1;
        $villages = Village::where('district_id', $selectedDistrictId)->orderBy('name', 'ASC')->get();

        $defaultOrgs = ['SATGAS', 'BANKOM', 'SAR MTA', 'TIM PARKIR', 'ELFATA', 'TIM IKHROM'];
        $customOrgs  = Organisasi::select('organization_name')
            ->distinct()
            ->whereNotIn('organization_name', $defaultOrgs)
            ->whereNotNull('organization_name')
            ->where('organization_name', '!=', '')
            ->orderBy('organization_name', 'ASC')
            ->pluck('organization_name')
            ->toArray();

        return view('admin.pemuda.form', [
            'title'           => 'Edit Data Pemuda: ' . $pemuda->name,
            'isEdit'          => true,
            'pemuda'          => $pemuda,
            'wilayahList'     => $wilayahList,
            'cabangList'      => $cabangList,
            'districts'       => District::where('regency_id', 3314)->orderBy('name', 'ASC')->get(),
            'villages'        => $villages,
            'educationLevels' => EducationLevel::orderBy('id', 'ASC')->get(),
            'jobStatuses'     => JobStatus::orderBy('id', 'ASC')->get(),
            'skills'          => Skill::orderBy('name', 'ASC')->get(),
            'interests'       => Interest::orderBy('name', 'ASC')->get(),
            'customOrgs'      => $customOrgs,
            'user'            => session()->all(),
            'scope'           => $scope,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $scope  = $this->getScope();
        $pemuda = Pemuda::getPemudaDetail($id, $scope);

        if (!$pemuda) {
            abort(404, 'Data pemuda tidak ditemukan.');
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
            'foto'               => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
        ];

        $request->validate($rules);

        $cabangId = (int) $request->input('cabang_id');
        if ($scope['role'] === 'admin_cabang' && (int) $scope['cabang_id'] !== $cabangId) {
            abort(403, 'Anda hanya dapat mengubah data pada cabang Anda sendiri.');
        }

        DB::beginTransaction();
        try {
            $fotoFilename = $pemuda->foto;
            if ($request->hasFile('foto') && $request->file('foto')->isValid()) {
                $fotoFile = $request->file('foto');
                $ext = strtolower($fotoFile->extension() ?: $fotoFile->getClientOriginalExtension());
                $fotoFilename = 'foto_' . date('YmdHis') . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                $fotoFile->move(public_path('uploads/pemuda'), $fotoFilename);
            }

            $bloodType = $request->input('blood_type');
            if ($bloodType === '-' || $bloodType === 'tidak_tahu') {
                $bloodType = null;
            }

            $pemuda->update([
                'cabang_id'         => $cabangId,
                'name'              => trim((string) $request->input('name')),
                'gender'            => (string) $request->input('gender'),
                'marital_status'    => $request->input('marital_status'),
                'blood_type'        => $bloodType,
                'birth_place'       => $request->input('birth_place'),
                'birth_date'        => $request->input('birth_date'),
                'phone'             => $request->input('phone'),
                'email'             => $request->input('email') ?: null,
                'status_verifikasi' => $pemuda->status_verifikasi, // Immutable manually per AGENTS.md Rule 16
                'foto'              => $fotoFilename,
            ]);

            Alamat::updateOrCreate(
                ['pemuda_id' => $pemuda->id],
                [
                    'province_id'    => 33,
                    'regency_id'     => 3314,
                    'district_id'    => (int) $request->input('district_id'),
                    'village_id'     => (int) $request->input('village_id'),
                    'dusun'          => $request->input('dusun') ?: null,
                    'rt'             => $request->input('rt') ?: null,
                    'rw'             => $request->input('rw') ?: null,
                    'address_detail' => $request->input('address_detail'),
                ]
            );

            Pendidikan::updateOrCreate(
                ['pemuda_id' => $pemuda->id],
                [
                    'education_level_id' => (int) $request->input('education_level_id'),
                    'school_name'        => $request->input('school_name'),
                    'major'              => $request->input('major') ?: null,
                    'education_status'   => $request->input('education_status'),
                    'graduation_year'    => $request->input('graduation_year') ?: null,
                ]
            );

            Pekerjaan::updateOrCreate(
                ['pemuda_id' => $pemuda->id],
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

            Organisasi::where('pemuda_id', $pemuda->id)->delete();
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
                            'pemuda_id'         => $pemuda->id,
                            'organization_name' => $cleanOrg,
                        ]);
                    }
                }
            }

            PemudaSkill::where('pemuda_id', $pemuda->id)->delete();
            $skillsInput = $request->input('skills', []);
            $customSkillsInput = $request->input('custom_skills', []);
            $selectedSkillIds = [];
            $customSkillNames = [];

            if (is_array($skillsInput)) {
                foreach ($skillsInput as $item) {
                    if (is_numeric($item)) {
                        $skId = (int) $item;
                        if ($skId > 0) $selectedSkillIds[] = $skId;
                    } else {
                        $cleanName = trim((string) $item);
                        if ($cleanName !== '') $customSkillNames[] = $cleanName;
                    }
                }
            }
            if (!empty($customSkillsInput)) {
                $rawList = is_array($customSkillsInput) ? $customSkillsInput : explode(',', (string) $customSkillsInput);
                foreach ($rawList as $rawName) {
                    $cleanName = trim((string) $rawName);
                    if ($cleanName !== '') $customSkillNames[] = $cleanName;
                }
            }
            foreach ($customSkillNames as $name) {
                $cleanName = trim($name);
                if ($cleanName === '') continue;
                $existingSkill = Skill::where('name', $cleanName)
                    ->orWhereRaw('LOWER(name) = ?', [mb_strtolower($cleanName)])
                    ->first();
                if (!$existingSkill) {
                    $existingSkill = Skill::create(['name' => ucwords($cleanName)]);
                }
                if ($existingSkill && !in_array($existingSkill->id, $selectedSkillIds, true)) {
                    $selectedSkillIds[] = $existingSkill->id;
                }
            }
            foreach (array_unique($selectedSkillIds) as $skId) {
                PemudaSkill::create([
                    'pemuda_id' => $pemuda->id,
                    'skill_id'  => $skId,
                    'level'     => 'menengah',
                ]);
            }

            PemudaInterest::where('pemuda_id', $pemuda->id)->delete();
            $interestsInput = $request->input('interests', []);
            $customInterestsInput = $request->input('custom_interests', []);
            $selectedInterestIds = [];
            $customInterestNames = [];

            if (is_array($interestsInput)) {
                foreach ($interestsInput as $item) {
                    if (is_numeric($item)) {
                        $intId = (int) $item;
                        if ($intId > 0) $selectedInterestIds[] = $intId;
                    } else {
                        $cleanName = trim((string) $item);
                        if ($cleanName !== '') $customInterestNames[] = $cleanName;
                    }
                }
            }
            if (!empty($customInterestsInput)) {
                $rawList = is_array($customInterestsInput) ? $customInterestsInput : explode(',', (string) $customInterestsInput);
                foreach ($rawList as $rawName) {
                    $cleanName = trim((string) $rawName);
                    if ($cleanName !== '') $customInterestNames[] = $cleanName;
                }
            }
            foreach ($customInterestNames as $name) {
                $cleanName = trim($name);
                if ($cleanName === '') continue;
                $existingInt = Interest::where('name', $cleanName)
                    ->orWhereRaw('LOWER(name) = ?', [mb_strtolower($cleanName)])
                    ->first();
                if (!$existingInt) {
                    $existingInt = Interest::create(['name' => ucwords($cleanName)]);
                }
                if ($existingInt && !in_array($existingInt->id, $selectedInterestIds, true)) {
                    $selectedInterestIds[] = $existingInt->id;
                }
            }
            foreach (array_unique($selectedInterestIds) as $intId) {
                PemudaInterest::create([
                    'pemuda_id'   => $pemuda->id,
                    'interest_id' => $intId,
                ]);
            }

            DB::commit();

            return redirect()->route('admin.pemuda.detail', $pemuda->id)
                ->with('success', 'Data pemuda berhasil diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('[PemudaController] Update Error: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    public function verifikasi(Request $request, int $id, MtaSyncService $syncService)
    {
        $scope  = $this->getScope();
        $pemuda = Pemuda::getPemudaDetail($id, $scope);

        if (!$pemuda) {
            return redirect()->back()->with('error', 'Data pemuda tidak ditemukan atau akses ditolak.');
        }

        // Per Rule 16: Status verifikasi ditentukan secara otomatis melalui sinkronisasi API MTA Pusat
        $syncResult = $syncService->syncSinglePemuda($pemuda->id, auth()->id());

        if (($syncResult['success'] ?? false) && ($syncResult['matched'] ?? false)) {
            return redirect()->back()->with('success', "Verifikasi Berhasil: Data pemuda '{$pemuda->name}' telah sinkron dan terverifikasi dengan database MTA Pusat.");
        }

        $detailMsg = $syncResult['message'] ?? "Data '{$pemuda->name}' tidak ditemukan atau belum tercatat di database MTA Pusat.";
        return redirect()->back()->with('warning', "Verifikasi Sistem: {$detailMsg} Status data tetap Belum Terverifikasi (pending).");
    }

    public function archive(Request $request, int $id)
    {
        $scope  = $this->getScope();
        $pemuda = Pemuda::getPemudaDetail($id, $scope);

        if (!$pemuda) {
            return redirect()->back()->with('error', 'Data pemuda tidak ditemukan atau akses ditolak.');
        }

        $current = $pemuda->status_data;
        $newStatus = $current === 'active' ? 'archived' : 'active';
        $pemuda->update(['status_data' => $newStatus]);

        $msg = $newStatus === 'archived' ? 'diarsipkan' : 'diaktifkan kembali';
        return redirect()->back()->with('success', "Data pemuda berhasil {$msg}.");
    }

    public function delete(int $id)
    {
        $scope  = $this->getScope();
        $pemuda = Pemuda::getPemudaDetail($id, $scope);

        if (!$pemuda) {
            return redirect()->back()->with('error', 'Data pemuda tidak ditemukan atau akses ditolak.');
        }

        DB::beginTransaction();
        try {
            Alamat::where('pemuda_id', $id)->delete();
            Pendidikan::where('pemuda_id', $id)->delete();
            Pekerjaan::where('pemuda_id', $id)->delete();
            Organisasi::where('pemuda_id', $id)->delete();
            PemudaSkill::where('pemuda_id', $id)->delete();
            PemudaInterest::where('pemuda_id', $id)->delete();
            $pemuda->delete();

            DB::commit();
            return redirect()->route('admin.pemuda.index')->with('success', 'Data pemuda berhasil dihapus.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    public function export(PemudaExportService $exportService)
    {
        $scope = $this->getScope();

        $wilayahQuery = Wilayah::orderBy('id', 'ASC');
        if (in_array($scope['role'], ['admin_wilayah', 'admin_wilayah_pemuda', 'admin_cabang'], true) && !empty($scope['wilayah_id'])) {
            $wilayahQuery->where('id', (int) $scope['wilayah_id']);
        }
        $wilayahList = $wilayahQuery->get();

        $cabangQuery = Cabang::orderBy('name', 'ASC');
        if ($scope['role'] === 'admin_cabang' && !empty($scope['cabang_id'])) {
            $cabangQuery->where('id', (int) $scope['cabang_id']);
        } elseif (in_array($scope['role'], ['admin_wilayah', 'admin_wilayah_pemuda'], true) && !empty($scope['wilayah_id'])) {
            $cabangQuery->where('wilayah_id', (int) $scope['wilayah_id']);
        }
        $cabangList = $cabangQuery->get();

        return view('admin.pemuda.export', [
            'title'               => 'Export Data Pemuda',
            'categorizedColumns' => $exportService->getCategorizedColumns(),
            'presets'             => $exportService->getPresets(),
            'wilayahList'         => $wilayahList,
            'cabangList'          => $cabangList,
            'educationLevels'     => EducationLevel::all(),
            'jobStatuses'         => JobStatus::all(),
            'skills'              => Skill::orderBy('name', 'ASC')->get(),
            'interests'           => Interest::orderBy('name', 'ASC')->get(),
            'user'                => session()->all(),
            'scope'               => $scope,
        ]);
    }

    public function exportDownload(Request $request, PemudaExportService $exportService)
    {
        $scope = $this->getScope();

        $filters = [
            'search'             => $request->input('search'),
            'wilayah_id'         => $request->input('wilayah_id'),
            'cabang_id'          => $request->input('cabang_id'),
            'gender'             => $request->input('gender'),
            'marital_status'     => $request->input('marital_status'),
            'blood_type'         => $request->input('blood_type'),
            'status_verifikasi'  => $request->input('status_verifikasi'),
            'status_data'        => $request->input('status_data') ?? 'active',
            'education_level_id' => $request->input('education_level_id'),
            'job_status_id'      => $request->input('job_status_id'),
            'skill_id'           => $request->input('skill_id'),
            'interest_id'        => $request->input('interest_id'),
            'organization_name'  => $request->input('organization_name'),
            'min_age'            => $request->input('min_age'),
            'max_age'            => $request->input('max_age'),
            'start_date'         => $request->input('start_date'),
            'end_date'           => $request->input('end_date'),
        ];

        if ($filters['status_data'] === 'all') {
            unset($filters['status_data']);
        }

        $columns = $request->input('columns', []);
        if (empty($columns)) {
            $columns = $exportService->getPreset('default');
        }

        $format = strtolower((string) $request->input('format', 'xlsx'));
        $filename = 'data_pemuda_' . date('Ymd_His');

        if ($format === 'csv') {
            $csv = $exportService->exportToCsv($columns, $filters, $scope);
            return response($csv, 200, [
                'Content-Type'        => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '.csv"',
            ]);
        }

        $spreadsheet = $exportService->exportToExcel($columns, $filters, $scope);
        $writer      = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename . '.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function exportCount(Request $request, PemudaExportService $exportService)
    {
        $scope   = $this->getScope();
        $filters = $request->all();
        if (($filters['status_data'] ?? '') === 'all') {
            unset($filters['status_data']);
        }
        $count = $exportService->countExportData($filters, $scope);

        return response()->json(['count' => $count]);
    }

    public function cetak(int $id)
    {
        $scope  = $this->getScope();
        $pemuda = Pemuda::getPemudaDetail($id, $scope);

        if (!$pemuda) {
            abort(404, 'Data pemuda tidak ditemukan.');
        }

        return view('admin.pemuda.cetak', [
            'title'  => 'Cetak Biodata: ' . $pemuda->name,
            'pemuda' => $pemuda,
        ]);
    }

    public function import()
    {
        return view('admin.pemuda.import', [
            'title'      => 'Import Data Pemuda dari Excel',
            'cabangList' => Cabang::orderBy('name', 'ASC')->get(),
            'user'       => session()->all(),
        ]);
    }

    public function prosesImport(Request $request, PemudaImportService $importService)
    {
        $request->validate([
            'file_excel' => 'required|file|mimes:xlsx,xls|max:10240',
        ]);

        $file = $request->file('file_excel');
        $path = $file->getRealPath();

        $options = [
            'dry_run'           => (bool) $request->input('dry_run'),
            'update_existing'   => (bool) $request->input('update_existing', true),
            'default_cabang_id' => $request->input('default_cabang_id'),
            'user_id'           => auth()->id(),
        ];

        $result = $importService->importExcel($path, $options);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($result);
        }

        if ($result['success']) {
            return redirect()->route('admin.pemuda.import')->with('import_result', $result);
        }

        return redirect()->back()->with('error', $result['message']);
    }

    public function templateImport(PemudaImportService $importService)
    {
        $spreadsheet = $importService->generateTemplate();
        $writer      = new Xlsx($spreadsheet);
        $filename    = 'template_import_pemuda_' . date('Ymd') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function backup(PemudaBackupService $backupService)
    {
        return view('admin.pemuda.backup', [
            'title'         => 'Backup & Reset Data Pemuda',
            'countsSummary' => $backupService->getCountsSummary(),
            'savedBackups'  => $backupService->listSavedBackups(),
            'user'          => session()->all(),
        ]);
    }

    public function generateBackup(Request $request, PemudaBackupService $backupService)
    {
        $type   = $request->input('type', 'sql');
        $result = $type === 'json'
            ? $backupService->generateJsonBackup(true)
            : $backupService->generateSqlBackup(true);

        return redirect()->route('admin.pemuda.backup')
            ->with('success', "File backup ({$result['filename']}) berhasil dibuat dan disimpan di server.");
    }

    public function downloadBackup(string $filename, PemudaBackupService $backupService)
    {
        $filePath = $backupService->getBackupFilePath($filename);
        if (!$filePath || !file_exists($filePath)) {
            return redirect()->back()->with('error', 'File backup tidak ditemukan.');
        }

        return response()->download($filePath, basename($filePath));
    }

    public function deleteBackupFile(string $filename, PemudaBackupService $backupService)
    {
        if ($backupService->deleteBackupFile($filename)) {
            return redirect()->route('admin.pemuda.backup')->with('success', "File backup {$filename} berhasil dihapus.");
        }
        return redirect()->back()->with('error', 'Gagal menghapus file backup.');
    }

    public function hapusSemua(Request $request, PemudaBackupService $backupService)
    {
        $confirm = (string) $request->input('confirmation');
        $result  = $backupService->clearAllYouthData(auth()->id(), $confirm);

        if ($result['success']) {
            return redirect()->route('admin.pemuda.backup')->with('success', $result['message']);
        }

        return redirect()->back()->with('error', $result['message']);
    }
}
