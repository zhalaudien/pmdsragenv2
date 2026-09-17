<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Pemuda extends Model
{
    protected $table = 'pemuda';

    protected $fillable = [
        'cabang_id',
        'registration_number',
        'name',
        'gender',
        'marital_status',
        'blood_type',
        'birth_place',
        'birth_date',
        'phone',
        'email',
        'status_verifikasi',
        'status_data',
        'mta_warga_uuid',
        'mta_status_warga',
        'mta_ayah_uuid',
        'mta_ibu_uuid',
        'mta_foto_url',
        'foto',
        'mta_synced_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'birth_date'    => 'date:Y-m-d',
            'mta_synced_at' => 'datetime',
        ];
    }

    // Relationships
    public function cabang()
    {
        return $this->belongsTo(Cabang::class, 'cabang_id');
    }

    public function alamat()
    {
        return $this->hasOne(Alamat::class, 'pemuda_id');
    }

    public function pendidikan()
    {
        return $this->hasOne(Pendidikan::class, 'pemuda_id');
    }

    public function pekerjaan()
    {
        return $this->hasOne(Pekerjaan::class, 'pemuda_id');
    }

    public function organisasi()
    {
        return $this->hasMany(Organisasi::class, 'pemuda_id')->orderBy('id', 'ASC');
    }

    public function skills()
    {
        return $this->belongsToMany(Skill::class, 'pemuda_skills', 'pemuda_id', 'skill_id')->withPivot('level');
    }

    public function pemudaSkills()
    {
        return $this->hasMany(PemudaSkill::class, 'pemuda_id');
    }

    public function interests()
    {
        return $this->belongsToMany(Interest::class, 'pemuda_interests', 'pemuda_id', 'interest_id');
    }

    public function pemudaInterests()
    {
        return $this->hasMany(PemudaInterest::class, 'pemuda_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Generate unique registration number
     * Format: IdPerwakilanIdCabangtanggallahirRandomNomor
     * Contoh: 8601200005178234 (Perwakilan: 86, Cabang: 01, Tgl Lahir: 20000517, Random: 4 digit 8234)
     */
    public static function generateRegistrationNumber(?int $cabangId = null, ?string $birthDate = null): string
    {
        $perwakilanCode = '86';
        $cabangCode     = '01';

        if (!empty($cabangId)) {
            $cabang = Cabang::find($cabangId);
            if ($cabang) {
                if (!empty($cabang->code) && preg_match('/^(\d+)\.(\d+)$/', trim($cabang->code), $matches)) {
                    $perwakilanCode = $matches[1];
                    $cabangCode     = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
                } else {
                    $cabangCode = str_pad((string) ($cabang->id ?? 1), 2, '0', STR_PAD_LEFT);
                }
            }
        }

        if (!empty($birthDate)) {
            $birthDateStr = trim((string) $birthDate);
            if (preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $birthDateStr, $dm)) {
                $birthCode = $dm[1] . $dm[2] . $dm[3];
            } elseif (preg_match('/^(\d{2})[\/\-](\d{2})[\/\-](\d{4})/', $birthDateStr, $dm)) {
                $birthCode = $dm[3] . $dm[2] . $dm[1];
            } elseif (preg_match('/^\d{8}$/', $birthDateStr)) {
                $birthCode = $birthDateStr;
            } else {
                $ts = strtotime($birthDateStr);
                $birthCode = ($ts !== false) ? date('Ymd', $ts) : date('Ymd');
            }
        } else {
            $birthCode = date('Ymd');
        }

        $baseNumber = $perwakilanCode . $cabangCode . $birthCode;

        do {
            $randomCode = str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
            $candidate  = $baseNumber . $randomCode;
            $count = static::where('registration_number', $candidate)->count();
        } while ($count > 0);

        return $candidate;
    }

    /**
     * Cari pemuda untuk verifikasi/pengecekan data
     */
    public static function findExistingPemuda(string $name, ?string $gender, string $birthDate, int $cabangId, ?int $excludeId = null): ?self
    {
        $cleanName      = trim($name);
        $cleanGender    = $gender ? trim(strtoupper($gender)) : null;
        $cleanBirthDate = trim($birthDate);

        if ($cleanName === '' || $cleanBirthDate === '' || $cabangId <= 0) {
            return null;
        }

        $timestamp = strtotime($cleanBirthDate);
        $formattedDate = ($timestamp !== false && $timestamp > 0) ? date('Y-m-d', $timestamp) : $cleanBirthDate;

        $query = static::where('cabang_id', $cabangId)
            ->where('birth_date', $formattedDate)
            ->whereRaw('LOWER(TRIM(name)) = ?', [strtolower($cleanName)]);

        if (!empty($cleanGender) && in_array($cleanGender, ['L', 'P'], true)) {
            $query->where('gender', $cleanGender);
        }

        if ($excludeId !== null && $excludeId > 0) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->first();
    }

    public static function findDuplicate(string $name, string $birthDate, int $cabangId, ?int $excludeId = null, ?string $gender = null): ?self
    {
        return static::findExistingPemuda($name, $gender, $birthDate, $cabangId, $excludeId);
    }

    public static function findByMtaWargaUuid(string $uuid): ?self
    {
        $cleanUuid = trim($uuid);
        if ($cleanUuid === '') {
            return null;
        }
        return static::where('mta_warga_uuid', $cleanUuid)->first();
    }

    /**
     * Apply Scope based on User Role
     */
    public function scopeForUserScope($query, array $scope = [])
    {
        if (isset($scope['role'])) {
            $role = $scope['role'];
            if ($role === 'admin_wilayah' && !empty($scope['wilayah_id'])) {
                $query->whereHas('cabang', function ($q) use ($scope) {
                    $q->where('wilayah_id', (int) $scope['wilayah_id']);
                });
            } elseif ($role === 'admin_wilayah_pemuda' && !empty($scope['wilayah_id'])) {
                $query->where('gender', 'L')
                      ->whereHas('cabang', function ($q) use ($scope) {
                          $q->where('wilayah_id', (int) $scope['wilayah_id']);
                      });
            } elseif ($role === 'admin_cabang' && !empty($scope['cabang_id'])) {
                $query->where('cabang_id', (int) $scope['cabang_id']);
            } elseif ($role === 'admin_pemuda') {
                $query->where('gender', 'L');
            } elseif ($role === 'admin_pemudi') {
                $query->where('gender', 'P');
            }
        }
        return $query;
    }

    /**
     * Base query for filtered youth list
     */
    public function scopeFiltered($query, array $filters = [], array $scope = [])
    {
        // 1. Enforce Role Scope first
        $this->scopeForUserScope($query, $scope);

        // 2. Apply filters
        if (!empty($filters['search'])) {
            $s = '%' . trim($filters['search']) . '%';
            $query->where(function ($q) use ($s) {
                $q->where('name', 'LIKE', $s)
                  ->orWhere('registration_number', 'LIKE', $s)
                  ->orWhere('phone', 'LIKE', $s)
                  ->orWhere('email', 'LIKE', $s)
                  ->orWhereHas('pekerjaan', function ($pq) use ($s) {
                      $pq->where('job_title', 'LIKE', $s)
                         ->orWhere('company_name', 'LIKE', $s)
                         ->orWhere('business_name', 'LIKE', $s);
                  })
                  ->orWhereHas('alamat', function ($aq) use ($s) {
                      $aq->where('address_detail', 'LIKE', $s);
                  });
            });
        }

        $role = $scope['role'] ?? 'superadmin';
        if (in_array($role, ['superadmin', 'admin_pemuda', 'admin_pemudi'], true) && !empty($filters['wilayah_id'])) {
            $query->whereHas('cabang', function ($cq) use ($filters) {
                $cq->where('wilayah_id', (int) $filters['wilayah_id']);
            });
        }

        if ($role !== 'admin_cabang' && !empty($filters['cabang_id'])) {
            $query->where('cabang_id', (int) $filters['cabang_id']);
        }

        if (in_array($role, ['admin_wilayah_pemuda', 'admin_pemuda'], true)) {
            $filters['gender'] = 'L';
        } elseif ($role === 'admin_pemudi') {
            $filters['gender'] = 'P';
        }

        if (!empty($filters['gender'])) {
            $query->where('gender', $filters['gender']);
        }

        if (!empty($filters['marital_status'])) {
            $query->where('marital_status', $filters['marital_status']);
        }

        if (!empty($filters['blood_type'])) {
            if ($filters['blood_type'] === 'unknown') {
                $query->where(function ($q) {
                    $q->whereNull('blood_type')
                      ->orWhere('blood_type', '')
                      ->orWhere('blood_type', 'tidak_tahu');
                });
            } else {
                $query->where('blood_type', $filters['blood_type']);
            }
        }

        if (!empty($filters['status_verifikasi'])) {
            $query->where('status_verifikasi', $filters['status_verifikasi']);
        }

        if (!empty($filters['status_data'])) {
            $query->where('status_data', $filters['status_data']);
        }

        if (!empty($filters['education_level_id'])) {
            $query->whereHas('pendidikan', function ($eq) use ($filters) {
                $eq->where('education_level_id', (int) $filters['education_level_id']);
            });
        }

        if (!empty($filters['job_status_id'])) {
            $query->whereHas('pekerjaan', function ($jq) use ($filters) {
                $jq->where('job_status_id', (int) $filters['job_status_id']);
            });
        }

        if (!empty($filters['start_date'])) {
            $query->where('created_at', '>=', $filters['start_date'] . ' 00:00:00');
        }

        if (!empty($filters['end_date'])) {
            $query->where('created_at', '<=', $filters['end_date'] . ' 23:59:59');
        }

        if (!empty($filters['skill_id'])) {
            $skillIds = is_array($filters['skill_id']) ? array_filter(array_map('intval', $filters['skill_id'])) : [(int) $filters['skill_id']];
            if (!empty($skillIds)) {
                $query->whereHas('skills', function ($sq) use ($skillIds) {
                    $sq->whereIn('skills.id', $skillIds);
                });
            }
        }

        if (!empty($filters['interest_id'])) {
            $interestIds = is_array($filters['interest_id']) ? array_filter(array_map('intval', $filters['interest_id'])) : [(int) $filters['interest_id']];
            if (!empty($interestIds)) {
                $query->whereHas('interests', function ($iq) use ($interestIds) {
                    $iq->whereIn('interests.id', $interestIds);
                });
            }
        }

        if (!empty($filters['organization_name'])) {
            $orgName = trim((string) $filters['organization_name']);
            if ($orgName !== '') {
                $query->whereHas('organisasi', function ($oq) use ($orgName) {
                    $oq->where('organization_name', 'LIKE', '%' . $orgName . '%');
                });
            }
        }

        if (!empty($filters['min_age'])) {
            $maxBirthDate = date('Y-m-d', strtotime('-' . (int) $filters['min_age'] . ' years'));
            $query->where('birth_date', '<=', $maxBirthDate);
        }

        if (!empty($filters['max_age'])) {
            $minBirthDate = date('Y-m-d', strtotime('-' . ((int) $filters['max_age'] + 1) . ' years +1 day'));
            $query->where('birth_date', '>=', $minBirthDate);
        }

        return $query;
    }

    /**
     * Ambil data pemuda lengkap dengan detail relasi
     */
    public static function getPemudaDetail(int $id, array $scope = []): ?self
    {
        $query = static::with([
            'cabang.wilayah',
            'alamat.province',
            'alamat.regency',
            'alamat.district',
            'alamat.village',
            'pendidikan.educationLevel',
            'pekerjaan.jobStatus',
            'organisasi',
            'skills',
            'interests',
            'creator',
        ])->where('id', $id);

        (new static)->scopeForUserScope($query, $scope);

        return $query->first();
    }

    /**
     * Hitung total per status
     */
    public static function getCountsSummary(array $scope = []): array
    {
        $base = (new static)->newQuery();
        (new static)->scopeForUserScope($base, $scope);

        $totalAll = (clone $base)->count();
        $verified = (clone $base)->where('status_verifikasi', 'verified')->count();
        $pending  = (clone $base)->where('status_verifikasi', 'pending')->count();
        $active   = (clone $base)->where('status_data', 'active')->count();
        $archived = (clone $base)->where('status_data', 'archived')->count();

        return [
            'total'    => $totalAll,
            'verified' => $verified,
            'pending'  => $pending,
            'rejected' => 0,
            'active'   => $active,
            'archived' => $archived,
        ];
    }

    /**
     * Data statistik untuk dashboard Super Admin & Admin
     */
    public static function getDashboardStats(array $scope = []): array
    {
        $summary = static::getCountsSummary($scope);

        $role      = $scope['role'] ?? 'superadmin';
        $wilayahId = !empty($scope['wilayah_id']) ? (int) $scope['wilayah_id'] : null;
        $cabangId  = !empty($scope['cabang_id']) ? (int) $scope['cabang_id'] : null;

        if (in_array($role, ['superadmin', 'admin_pemuda', 'admin_pemudi'], true)) {
            $totalWilayah = Wilayah::count();
            $totalCabang  = Cabang::count();
            $totalUsers   = User::where('status', 1)->count();
        } elseif (in_array($role, ['admin_wilayah', 'admin_wilayah_pemuda'], true)) {
            $totalWilayah = 1;
            $totalCabang  = Cabang::where('wilayah_id', $wilayahId)->count();
            $totalUsers   = User::where('status', 1)->where('wilayah_id', $wilayahId)->count();
        } else {
            $totalWilayah = 1;
            $totalCabang  = 1;
            $totalUsers   = User::where('status', 1)->where('cabang_id', $cabangId)->count();
        }

        // Gender stats
        $baseQuery = (new static)->newQuery()->where('status_data', 'active');
        (new static)->scopeForUserScope($baseQuery, $scope);

        $genderStats = (clone $baseQuery)->select('gender', DB::raw('COUNT(id) as total'))
            ->groupBy('gender')
            ->get();

        $genderData = ['L' => 0, 'P' => 0];
        foreach ($genderStats as $row) {
            $genderData[$row->gender] = (int) $row->total;
        }

        // Marital status stats
        $maritalStats = (clone $baseQuery)->select('marital_status', DB::raw('COUNT(id) as total'))
            ->groupBy('marital_status')
            ->get();

        $maritalData = [
            'belum_menikah' => 0,
            'sudah_menikah' => 0,
            'duda'          => 0,
            'janda'         => 0,
            'lajang'        => 0,
            'menikah'       => 0,
        ];
        foreach ($maritalStats as $row) {
            $key = $row->marital_status ?: 'belum_menikah';
            $maritalData[$key] = (int) $row->total;
        }
        $maritalData['lajang']  = $maritalData['belum_menikah'];
        $maritalData['menikah'] = $maritalData['sudah_menikah'];

        // Wilayah Statistics
        $genderJoin = '';
        if (in_array($role, ['admin_wilayah_pemuda', 'admin_pemuda'], true)) {
            $genderJoin = " AND pemuda.gender = 'L'";
        } elseif ($role === 'admin_pemudi') {
            $genderJoin = " AND pemuda.gender = 'P'";
        }

        $wilayahQuery = DB::table('wilayah')
            ->select('wilayah.id', 'wilayah.code', 'wilayah.name', DB::raw('COUNT(pemuda.id) as total'))
            ->leftJoin('cabang', 'cabang.wilayah_id', '=', 'wilayah.id')
            ->leftJoin('pemuda', function ($join) use ($genderJoin) {
                $join->on('pemuda.cabang_id', '=', 'cabang.id')
                     ->where('pemuda.status_data', '=', 'active');
                if ($genderJoin !== '') {
                    $join->whereRaw("1=1 {$genderJoin}");
                }
            })
            ->groupBy('wilayah.id', 'wilayah.code', 'wilayah.name')
            ->orderBy('wilayah.id', 'ASC');

        if (in_array($role, ['admin_wilayah', 'admin_wilayah_pemuda'], true) && $wilayahId) {
            $wilayahQuery->where('wilayah.id', $wilayahId);
        } elseif ($role === 'admin_cabang') {
            if ($wilayahId) {
                $wilayahQuery->where('wilayah.id', $wilayahId);
            }
            if ($cabangId) {
                $wilayahQuery->where('cabang.id', $cabangId);
            }
        }
        $wilayahStats = $wilayahQuery->get()->map(fn($item) => (array) $item)->toArray();

        // Top Cabang Stats
        $cabangQuery = DB::table('cabang')
            ->select('cabang.id', 'cabang.name', 'wilayah.name as wilayah_name', DB::raw('COUNT(pemuda.id) as total'))
            ->leftJoin('wilayah', 'wilayah.id', '=', 'cabang.wilayah_id')
            ->leftJoin('pemuda', function ($join) use ($genderJoin) {
                $join->on('pemuda.cabang_id', '=', 'cabang.id')
                     ->where('pemuda.status_data', '=', 'active');
                if ($genderJoin !== '') {
                    $join->whereRaw("1=1 {$genderJoin}");
                }
            })
            ->groupBy('cabang.id', 'cabang.name', 'wilayah.name')
            ->orderBy('total', 'DESC')
            ->limit(10);

        if (in_array($role, ['admin_wilayah', 'admin_wilayah_pemuda'], true) && $wilayahId) {
            $cabangQuery->where('cabang.wilayah_id', $wilayahId);
        } elseif ($role === 'admin_cabang' && $cabangId) {
            $cabangQuery->where('cabang.id', $cabangId);
        }
        $topCabangStats = $cabangQuery->get()->map(fn($item) => (array) $item)->toArray();

        // Education Stats
        $eduQuery = DB::table('education_levels')
            ->select('education_levels.id', 'education_levels.name', DB::raw('COUNT(pemuda.id) as total'))
            ->leftJoin('pendidikan', 'pendidikan.education_level_id', '=', 'education_levels.id')
            ->leftJoin('pemuda', function($join) use ($genderJoin) {
                $join->on('pemuda.id', '=', 'pendidikan.pemuda_id')
                     ->where('pemuda.status_data', '=', 'active');
                if ($genderJoin !== '') {
                    $join->whereRaw("1=1 {$genderJoin}");
                }
            })
            ->leftJoin('cabang', 'cabang.id', '=', 'pemuda.cabang_id');

        (new static)->scopeForUserScopeRaw($eduQuery, $scope);

        $educationStats = $eduQuery->groupBy('education_levels.id', 'education_levels.name')
            ->orderBy('education_levels.id', 'ASC')
            ->get()->map(fn($item) => (array) $item)->toArray();

        // Job Stats
        $jobQuery = DB::table('job_statuses')
            ->select('job_statuses.id', 'job_statuses.name', DB::raw('COUNT(pemuda.id) as total'))
            ->leftJoin('pekerjaan', 'pekerjaan.job_status_id', '=', 'job_statuses.id')
            ->leftJoin('pemuda', function($join) use ($genderJoin) {
                $join->on('pemuda.id', '=', 'pekerjaan.pemuda_id')
                     ->where('pemuda.status_data', '=', 'active');
                if ($genderJoin !== '') {
                    $join->whereRaw("1=1 {$genderJoin}");
                }
            })
            ->leftJoin('cabang', 'cabang.id', '=', 'pemuda.cabang_id');

        (new static)->scopeForUserScopeRaw($jobQuery, $scope);

        $jobStats = $jobQuery->groupBy('job_statuses.id', 'job_statuses.name')
            ->orderBy('job_statuses.id', 'ASC')
            ->get()->map(fn($item) => (array) $item)->toArray();

        // Blood Stats
        $bloodQuery = DB::table('pemuda')
            ->select(DB::raw('COALESCE(NULLIF(pemuda.blood_type, ""), "Tidak Tahu") as blood_type'), DB::raw('COUNT(pemuda.id) as total'))
            ->leftJoin('cabang', 'cabang.id', '=', 'pemuda.cabang_id')
            ->where('pemuda.status_data', 'active');

        (new static)->scopeForUserScopeRaw($bloodQuery, $scope);

        $bloodStats = $bloodQuery->groupBy('blood_type')
            ->orderBy('total', 'DESC')
            ->get()->map(fn($item) => (array) $item)->toArray();

        // Recent Registrations / Updates
        $recentQuery = DB::table('pemuda')
            ->select('pemuda.*', DB::raw('COALESCE(pemuda.updated_at, pemuda.created_at) as last_edited_at'), 'cabang.name as cabang_name', 'wilayah.name as wilayah_name')
            ->leftJoin('cabang', 'cabang.id', '=', 'pemuda.cabang_id')
            ->leftJoin('wilayah', 'wilayah.id', '=', 'cabang.wilayah_id')
            ->where('pemuda.status_data', 'active')
            ->orderBy(DB::raw('COALESCE(pemuda.updated_at, pemuda.created_at)'), 'DESC')
            ->orderBy('pemuda.id', 'DESC')
            ->limit(10);

        (new static)->scopeForUserScopeRaw($recentQuery, $scope);
        $recentUpdates = $recentQuery->get()->map(fn($item) => (array) $item)->toArray();

        return [
            'summary'             => $summary,
            'totalWilayah'        => $totalWilayah,
            'totalCabang'         => $totalCabang,
            'totalUsers'          => $totalUsers,
            'genderData'          => $genderData,
            'maritalData'         => $maritalData,
            'wilayahStats'        => $wilayahStats,
            'topCabangStats'      => $topCabangStats,
            'educationStats'      => $educationStats,
            'jobStats'            => $jobStats,
            'bloodStats'          => $bloodStats,
            'recentPemuda'        => $recentUpdates,
            'recentRegistrations' => $recentUpdates,
            'recentUpdates'       => $recentUpdates,
        ];
    }

    /**
     * Raw Query Scope helper for DB::table builders
     */
    protected function scopeForUserScopeRaw($builder, array $scope = [])
    {
        if (isset($scope['role'])) {
            $role = $scope['role'];
            if ($role === 'admin_wilayah' && !empty($scope['wilayah_id'])) {
                $builder->where('cabang.wilayah_id', (int) $scope['wilayah_id']);
            } elseif ($role === 'admin_wilayah_pemuda' && !empty($scope['wilayah_id'])) {
                $builder->where('cabang.wilayah_id', (int) $scope['wilayah_id'])
                        ->where('pemuda.gender', 'L');
            } elseif ($role === 'admin_cabang' && !empty($scope['cabang_id'])) {
                $builder->where('pemuda.cabang_id', (int) $scope['cabang_id']);
            } elseif ($role === 'admin_pemuda') {
                $builder->where('pemuda.gender', 'L');
            } elseif ($role === 'admin_pemudi') {
                $builder->where('pemuda.gender', 'P');
            }
        }
        return $builder;
    }

    /**
     * Raw Query Scope & Custom Filter helper for Persebaran
     */
    protected function scopeAndCustomFiltersRaw($builder, array $scope = [], array $filters = [])
    {
        $this->scopeForUserScopeRaw($builder, $scope);

        $role = $scope['role'] ?? 'superadmin';

        if (in_array($role, ['superadmin', 'admin_pemuda', 'admin_pemudi'], true) && !empty($filters['wilayah_id'])) {
            $builder->where('cabang.wilayah_id', (int) $filters['wilayah_id']);
        }

        if ($role !== 'admin_cabang' && !empty($filters['cabang_id'])) {
            $builder->where('pemuda.cabang_id', (int) $filters['cabang_id']);
        }

        if (!in_array($role, ['admin_pemuda', 'admin_pemudi', 'admin_wilayah_pemuda'], true) && !empty($filters['gender'])) {
            $builder->where('pemuda.gender', $filters['gender']);
        }

        $statusData = $filters['status_data'] ?? 'active';
        if ($statusData !== 'all') {
            $builder->where('pemuda.status_data', $statusData);
        }

        if (!empty($filters['blood_type'])) {
            $bt = strtolower(trim($filters['blood_type']));
            if ($bt === 'unknown') {
                $builder->where(function ($q) {
                    $q->whereNull('pemuda.blood_type')
                      ->orWhere('pemuda.blood_type', '')
                      ->orWhereRaw('LOWER(pemuda.blood_type) = ?', ['tidak_tahu']);
                });
            } else {
                $builder->where(function ($q) use ($bt) {
                    $q->whereRaw('LOWER(pemuda.blood_type) = ?', [$bt]);
                });
            }
        }

        return $builder;
    }

    /**
     * Data statistik persebaran komprehensif
     */
    public static function getPersebaranStats(array $scope = [], array $filters = []): array
    {
        $self = new static;

        // 1. Total Pemuda
        $builderTotal = DB::table('pemuda')->leftJoin('cabang', 'cabang.id', '=', 'pemuda.cabang_id');
        $self->scopeAndCustomFiltersRaw($builderTotal, $scope, $filters);
        $totalYouth = $builderTotal->count();

        // Gender breakdown
        $builderGender = DB::table('pemuda')
            ->select('pemuda.gender', DB::raw('COUNT(pemuda.id) as total'))
            ->leftJoin('cabang', 'cabang.id', '=', 'pemuda.cabang_id');
        $self->scopeAndCustomFiltersRaw($builderGender, $scope, $filters);
        $genderStatsRaw = $builderGender->groupBy('pemuda.gender')->get();
        $genderData = ['L' => 0, 'P' => 0];
        foreach ($genderStatsRaw as $row) {
            $genderData[$row->gender] = (int) $row->total;
        }

        // 2. Element Dakwah (Organisasi)
        $builderOrg = DB::table('organisasi')
            ->select('organisasi.organization_name', DB::raw('COUNT(DISTINCT organisasi.pemuda_id) as total'))
            ->join('pemuda', 'pemuda.id', '=', 'organisasi.pemuda_id')
            ->leftJoin('cabang', 'cabang.id', '=', 'pemuda.cabang_id');
        $self->scopeAndCustomFiltersRaw($builderOrg, $scope, $filters);
        $orgRows = $builderOrg->groupBy('organisasi.organization_name')
            ->orderBy('total', 'DESC')
            ->get();

        $orgMaster = [
            'satgas'     => ['name' => 'Satgas (Satuan Tugas)', 'badge' => 'Satgas', 'icon' => 'fas fa-shield-alt text-danger', 'color' => '#dc3545'],
            'bankom'     => ['name' => 'Bankom (Bantuan Komunikasi)', 'badge' => 'Bankom', 'icon' => 'fas fa-broadcast-tower text-primary', 'color' => '#007bff'],
            'parkir'     => ['name' => 'Tim Parkir', 'badge' => 'Parkir', 'icon' => 'fas fa-parking text-warning', 'color' => '#ffc107'],
            'pemuda'     => ['name' => 'Kepengurusan Pemuda', 'badge' => 'Pengurus Pemuda', 'icon' => 'fas fa-users text-success', 'color' => '#28a745'],
            'tim_ikhrom' => ['name' => 'Tim Ikhrom', 'badge' => 'Tim Ikhrom', 'icon' => 'fas fa-hands-helping text-purple', 'color' => '#6f42c1'],
            'tim ikhrom' => ['name' => 'Tim Ikhrom', 'badge' => 'Tim Ikhrom', 'icon' => 'fas fa-hands-helping text-purple', 'color' => '#6f42c1'],
        ];

        $palette = ['#e83e8c', '#20c997', '#fd7e14', '#17a2b8', '#6610f2', '#6c757d'];
        $pIdx = 0;
        $orgStats = [];
        foreach ($orgRows as $r) {
            $cleanKey = strtolower(trim($r->organization_name));
            if (isset($orgMaster[$cleanKey])) {
                $item = $orgMaster[$cleanKey];
                $name = $item['name'];
                if (isset($orgStats[$name])) {
                    $orgStats[$name]['total'] += (int) $r->total;
                } else {
                    $orgStats[$name] = [
                        'name'  => $name,
                        'badge' => $item['badge'],
                        'icon'  => $item['icon'],
                        'color' => $item['color'],
                        'total' => (int) $r->total,
                    ];
                }
            } else {
                $name = ucwords($cleanKey);
                $color = $palette[$pIdx % count($palette)];
                $pIdx++;
                if (isset($orgStats[$name])) {
                    $orgStats[$name]['total'] += (int) $r->total;
                } else {
                    $orgStats[$name] = [
                        'name'  => $name,
                        'badge' => 'Unit Khusus',
                        'icon'  => 'fas fa-flag text-info',
                        'color' => $color,
                        'total' => (int) $r->total,
                    ];
                }
            }
        }
        $orgStats = array_values($orgStats);

        // With org vs without
        $builderWithOrg = DB::table('organisasi')
            ->select(DB::raw('COUNT(DISTINCT organisasi.pemuda_id) as total'))
            ->join('pemuda', 'pemuda.id', '=', 'organisasi.pemuda_id')
            ->leftJoin('cabang', 'cabang.id', '=', 'pemuda.cabang_id');
        $self->scopeAndCustomFiltersRaw($builderWithOrg, $scope, $filters);
        $rowWithOrg = $builderWithOrg->first();
        $totalWithOrg = (int) ($rowWithOrg->total ?? 0);
        $totalWithoutOrg = max(0, $totalYouth - $totalWithOrg);

        // 3. Sekolah & Pendidikan
        $builderEduLevel = DB::table('education_levels')
            ->select('education_levels.id', 'education_levels.name', DB::raw('COUNT(DISTINCT pemuda.id) as total'))
            ->leftJoin('pendidikan', 'pendidikan.education_level_id', '=', 'education_levels.id')
            ->leftJoin('pemuda', 'pemuda.id', '=', 'pendidikan.pemuda_id')
            ->leftJoin('cabang', 'cabang.id', '=', 'pemuda.cabang_id');
        $self->scopeAndCustomFiltersRaw($builderEduLevel, $scope, $filters);
        $eduLevelStats = $builderEduLevel->groupBy('education_levels.id', 'education_levels.name')
            ->orderBy('education_levels.id', 'ASC')
            ->get()->map(fn($item) => (array) $item)->toArray();

        // Education Status
        $builderEduStatus = DB::table('pendidikan')
            ->select(DB::raw('COALESCE(NULLIF(pendidikan.education_status, ""), "belum_diisi") as status'), DB::raw('COUNT(DISTINCT pendidikan.pemuda_id) as total'))
            ->join('pemuda', 'pemuda.id', '=', 'pendidikan.pemuda_id')
            ->leftJoin('cabang', 'cabang.id', '=', 'pemuda.cabang_id');
        $self->scopeAndCustomFiltersRaw($builderEduStatus, $scope, $filters);
        $eduStatusRaw = $builderEduStatus->groupBy('status')->get();

        $eduStatusData = [
            'sedang_sekolah'  => ['label' => 'Sedang Menempuh (Aktif)', 'total' => 0, 'color' => '#17a2b8'],
            'sedang_menempuh' => ['label' => 'Sedang Menempuh (Aktif)', 'total' => 0, 'color' => '#17a2b8'],
            'lulus'           => ['label' => 'Sudah Lulus / Tamat', 'total' => 0, 'color' => '#28a745'],
            'putus_sekolah'   => ['label' => 'Putus Sekolah / Belum Lulus', 'total' => 0, 'color' => '#dc3545'],
            'belum_diisi'     => ['label' => 'Belum Tercatat', 'total' => 0, 'color' => '#6c757d'],
        ];
        foreach ($eduStatusRaw as $r) {
            $key = $r->status;
            if ($key === 'sedang_menempuh' || $key === 'sedang_sekolah') {
                $eduStatusData['sedang_sekolah']['total']  += (int) $r->total;
                $eduStatusData['sedang_menempuh']['total'] += (int) $r->total;
            } elseif ($key === 'belum_lulus' || $key === 'putus_sekolah') {
                $eduStatusData['putus_sekolah']['total'] += (int) $r->total;
            } elseif (isset($eduStatusData[$key])) {
                $eduStatusData[$key]['total'] += (int) $r->total;
            } else {
                $eduStatusData['belum_diisi']['total'] += (int) $r->total;
            }
        }

        // Top Schools
        $builderSchools = DB::table('pendidikan')
            ->select(DB::raw('TRIM(pendidikan.school_name) as school_name'), DB::raw('COUNT(DISTINCT pendidikan.pemuda_id) as total'))
            ->join('pemuda', 'pemuda.id', '=', 'pendidikan.pemuda_id')
            ->leftJoin('cabang', 'cabang.id', '=', 'pemuda.cabang_id')
            ->whereNotNull('pendidikan.school_name')
            ->where(DB::raw('TRIM(pendidikan.school_name)'), '!=', '');
        $self->scopeAndCustomFiltersRaw($builderSchools, $scope, $filters);
        $topSchools = $builderSchools->groupBy(DB::raw('TRIM(pendidikan.school_name)'))
            ->orderBy('total', 'DESC')
            ->limit(10)
            ->get()->map(fn($item) => (array) $item)->toArray();

        // Top Majors
        $builderMajors = DB::table('pendidikan')
            ->select(DB::raw('TRIM(pendidikan.major) as major_name'), DB::raw('COUNT(DISTINCT pendidikan.pemuda_id) as total'))
            ->join('pemuda', 'pemuda.id', '=', 'pendidikan.pemuda_id')
            ->leftJoin('cabang', 'cabang.id', '=', 'pemuda.cabang_id')
            ->whereNotNull('pendidikan.major')
            ->where(DB::raw('TRIM(pendidikan.major)'), '!=', '');
        $self->scopeAndCustomFiltersRaw($builderMajors, $scope, $filters);
        $topMajors = $builderMajors->groupBy(DB::raw('TRIM(pendidikan.major)'))
            ->orderBy('total', 'DESC')
            ->limit(10)
            ->get()->map(fn($item) => (array) $item)->toArray();

        // 4. Bakat & Keahlian (Skills)
        $builderSkills = DB::table('skills')
            ->select('skills.id', 'skills.name', DB::raw('COUNT(DISTINCT pemuda_skills.pemuda_id) as total'))
            ->join('pemuda_skills', 'pemuda_skills.skill_id', '=', 'skills.id')
            ->join('pemuda', 'pemuda.id', '=', 'pemuda_skills.pemuda_id')
            ->leftJoin('cabang', 'cabang.id', '=', 'pemuda.cabang_id');
        $self->scopeAndCustomFiltersRaw($builderSkills, $scope, $filters);
        $topSkills = $builderSkills->groupBy('skills.id', 'skills.name')
            ->orderBy('total', 'DESC')
            ->limit(10)
            ->get()->map(fn($item) => (array) $item)->toArray();

        // Skill Level Data
        $builderSkillLevel = DB::table('pemuda_skills')
            ->select(DB::raw('COALESCE(NULLIF(pemuda_skills.level, ""), "pemula") as level'), DB::raw('COUNT(pemuda_skills.pemuda_id) as total'))
            ->join('pemuda', 'pemuda.id', '=', 'pemuda_skills.pemuda_id')
            ->leftJoin('cabang', 'cabang.id', '=', 'pemuda.cabang_id');
        $self->scopeAndCustomFiltersRaw($builderSkillLevel, $scope, $filters);
        $skillLevelRaw = $builderSkillLevel->groupBy('level')->get();

        $skillLevelData = [
            'pemula'   => ['label' => 'Pemula (Basic)', 'total' => 0, 'color' => '#ffc107'],
            'menengah' => ['label' => 'Menengah (Intermediate)', 'total' => 0, 'color' => '#17a2b8'],
            'mahir'    => ['label' => 'Mahir (Advanced)', 'total' => 0, 'color' => '#28a745'],
        ];
        foreach ($skillLevelRaw as $r) {
            $lvl = strtolower($r->level);
            if (isset($skillLevelData[$lvl])) {
                $skillLevelData[$lvl]['total'] += (int) $r->total;
            }
        }

        $builderWithSkill = DB::table('pemuda_skills')
            ->select(DB::raw('COUNT(DISTINCT pemuda_skills.pemuda_id) as total'))
            ->join('pemuda', 'pemuda.id', '=', 'pemuda_skills.pemuda_id')
            ->leftJoin('cabang', 'cabang.id', '=', 'pemuda.cabang_id');
        $self->scopeAndCustomFiltersRaw($builderWithSkill, $scope, $filters);
        $rowWithSkill = $builderWithSkill->first();
        $totalWithSkill = (int) ($rowWithSkill->total ?? 0);
        $totalWithoutSkill = max(0, $totalYouth - $totalWithSkill);

        // 5. Minat (Interests)
        $builderInterests = DB::table('interests')
            ->select('interests.id', 'interests.name', DB::raw('COUNT(DISTINCT pemuda_interests.pemuda_id) as total'))
            ->join('pemuda_interests', 'pemuda_interests.interest_id', '=', 'interests.id')
            ->join('pemuda', 'pemuda.id', '=', 'pemuda_interests.pemuda_id')
            ->leftJoin('cabang', 'cabang.id', '=', 'pemuda.cabang_id');
        $self->scopeAndCustomFiltersRaw($builderInterests, $scope, $filters);
        $topInterests = $builderInterests->groupBy('interests.id', 'interests.name')
            ->orderBy('total', 'DESC')
            ->limit(10)
            ->get()->map(fn($item) => (array) $item)->toArray();

        // 6. Ketenagakerjaan & Wirausaha
        $builderJobs = DB::table('job_statuses')
            ->select('job_statuses.id', 'job_statuses.name', DB::raw('COUNT(DISTINCT pemuda.id) as total'))
            ->leftJoin('pekerjaan', 'pekerjaan.job_status_id', '=', 'job_statuses.id')
            ->leftJoin('pemuda', 'pemuda.id', '=', 'pekerjaan.pemuda_id')
            ->leftJoin('cabang', 'cabang.id', '=', 'pemuda.cabang_id');
        $self->scopeAndCustomFiltersRaw($builderJobs, $scope, $filters);
        $jobStats = $builderJobs->groupBy('job_statuses.id', 'job_statuses.name')
            ->orderBy('job_statuses.id', 'ASC')
            ->get()->map(fn($item) => (array) $item)->toArray();

        $builderWirausaha = DB::table('pekerjaan')
            ->select(DB::raw('COUNT(DISTINCT pekerjaan.pemuda_id) as total'))
            ->join('pemuda', 'pemuda.id', '=', 'pekerjaan.pemuda_id')
            ->leftJoin('cabang', 'cabang.id', '=', 'pemuda.cabang_id')
            ->where(function ($q) {
                $q->whereNotNull('pekerjaan.business_name')->where(DB::raw('TRIM(pekerjaan.business_name)'), '!=', '')
                  ->orWhere(function ($q2) {
                      $q2->whereNotNull('pekerjaan.business_field')->where(DB::raw('TRIM(pekerjaan.business_field)'), '!=', '');
                  });
            });
        $self->scopeAndCustomFiltersRaw($builderWirausaha, $scope, $filters);
        $rowWirausaha = $builderWirausaha->first();
        $totalWirausaha = (int) ($rowWirausaha->total ?? 0);

        $builderBizFields = DB::table('pekerjaan')
            ->select(DB::raw('TRIM(pekerjaan.business_field) as name'), DB::raw('COUNT(DISTINCT pekerjaan.pemuda_id) as total'))
            ->join('pemuda', 'pemuda.id', '=', 'pekerjaan.pemuda_id')
            ->leftJoin('cabang', 'cabang.id', '=', 'pemuda.cabang_id')
            ->whereNotNull('pekerjaan.business_field')
            ->where(DB::raw('TRIM(pekerjaan.business_field)'), '!=', '');
        $self->scopeAndCustomFiltersRaw($builderBizFields, $scope, $filters);
        $topBizFields = $builderBizFields->groupBy(DB::raw('TRIM(pekerjaan.business_field)'))
            ->orderBy('total', 'DESC')
            ->limit(8)
            ->get()->map(fn($item) => (array) $item)->toArray();

        // 7. Demografi Usia
        $builderAge = DB::table('pemuda')
            ->select(DB::raw('
                CASE 
                    WHEN pemuda.birth_date IS NULL OR pemuda.birth_date = "0000-00-00" THEN "unknown"
                    WHEN TIMESTAMPDIFF(YEAR, pemuda.birth_date, CURDATE()) < 17 THEN "under_17"
                    WHEN TIMESTAMPDIFF(YEAR, pemuda.birth_date, CURDATE()) BETWEEN 17 AND 21 THEN "17_21"
                    WHEN TIMESTAMPDIFF(YEAR, pemuda.birth_date, CURDATE()) BETWEEN 22 AND 25 THEN "22_25"
                    WHEN TIMESTAMPDIFF(YEAR, pemuda.birth_date, CURDATE()) BETWEEN 26 AND 30 THEN "26_30"
                    ELSE "over_30"
                END as age_group
            '), DB::raw('COUNT(pemuda.id) as total'))
            ->leftJoin('cabang', 'cabang.id', '=', 'pemuda.cabang_id');
        $self->scopeAndCustomFiltersRaw($builderAge, $scope, $filters);
        $ageStatsRaw = $builderAge->groupBy('age_group')->get();

        $ageData = [
            'under_17' => ['label' => '< 17 Tahun (Remaja)', 'total' => 0, 'color' => '#17a2b8'],
            '17_21'    => ['label' => '17 - 21 Tahun (Pemuda Awal)', 'total' => 0, 'color' => '#28a745'],
            '22_25'    => ['label' => '22 - 25 Tahun (Pemuda Produktif)', 'total' => 0, 'color' => '#007bff'],
            '26_30'    => ['label' => '26 - 30 Tahun (Pemuda Dewasa)', 'total' => 0, 'color' => '#ffc107'],
            'over_30'  => ['label' => '> 30 Tahun (Pemuda Senior)', 'total' => 0, 'color' => '#6c757d'],
            'unknown'  => ['label' => 'Belum Tercatat', 'total' => 0, 'color' => '#adb5bd'],
        ];
        foreach ($ageStatsRaw as $r) {
            if (isset($ageData[$r->age_group])) {
                $ageData[$r->age_group]['total'] = (int) $r->total;
            }
        }

        $builderAvgAge = DB::table('pemuda')
            ->select(DB::raw('ROUND(AVG(TIMESTAMPDIFF(YEAR, pemuda.birth_date, CURDATE())), 1) as avg_age'))
            ->leftJoin('cabang', 'cabang.id', '=', 'pemuda.cabang_id')
            ->whereNotNull('pemuda.birth_date')
            ->where('pemuda.birth_date', '!=', '0000-00-00');
        $self->scopeAndCustomFiltersRaw($builderAvgAge, $scope, $filters);
        $avgAgeRow = $builderAvgAge->first();
        $avgAge = !empty($avgAgeRow->avg_age) ? (float) $avgAgeRow->avg_age : 0.0;

        // 8. Sebaran Kecamatan
        $builderDistricts = DB::table('districts')
            ->select('districts.id', 'districts.name', DB::raw('COUNT(DISTINCT pemuda.id) as total'))
            ->leftJoin('alamat', 'alamat.district_id', '=', 'districts.id')
            ->leftJoin('pemuda', 'pemuda.id', '=', 'alamat.pemuda_id')
            ->leftJoin('cabang', 'cabang.id', '=', 'pemuda.cabang_id');
        $self->scopeAndCustomFiltersRaw($builderDistricts, $scope, $filters);
        $districtStats = $builderDistricts->where('districts.regency_id', 3314)
            ->groupBy('districts.id', 'districts.name')
            ->orderBy('total', 'DESC')
            ->limit(10)
            ->get()->map(fn($item) => (array) $item)->toArray();

        // 9. Sebaran Wilayah & Top Cabang
        $builderWilayah = DB::table('wilayah')
            ->select('wilayah.id', 'wilayah.code', 'wilayah.name', DB::raw('COUNT(DISTINCT pemuda.id) as total'))
            ->leftJoin('cabang', 'cabang.wilayah_id', '=', 'wilayah.id')
            ->leftJoin('pemuda', 'pemuda.cabang_id', '=', 'cabang.id');
        $self->scopeAndCustomFiltersRaw($builderWilayah, $scope, $filters);
        $wilayahStats = $builderWilayah->groupBy('wilayah.id', 'wilayah.code', 'wilayah.name')
            ->orderBy('wilayah.id', 'ASC')
            ->get()->map(fn($item) => (array) $item)->toArray();

        $builderCabang = DB::table('cabang')
            ->select('cabang.id', 'cabang.name', 'wilayah.name as wilayah_name', DB::raw('COUNT(DISTINCT pemuda.id) as total'))
            ->leftJoin('wilayah', 'wilayah.id', '=', 'cabang.wilayah_id')
            ->leftJoin('pemuda', 'pemuda.cabang_id', '=', 'cabang.id');
        $self->scopeAndCustomFiltersRaw($builderCabang, $scope, $filters);
        $topCabangStats = $builderCabang->groupBy('cabang.id', 'cabang.name', 'wilayah.name')
            ->orderBy('total', 'DESC')
            ->limit(10)
            ->get()->map(fn($item) => (array) $item)->toArray();

        // 10. Golongan Darah
        $builderBlood = DB::table('pemuda')
            ->select(DB::raw('
                CASE 
                    WHEN UPPER(TRIM(pemuda.blood_type)) = "A" THEN "A"
                    WHEN UPPER(TRIM(pemuda.blood_type)) = "B" THEN "B"
                    WHEN UPPER(TRIM(pemuda.blood_type)) = "AB" THEN "AB"
                    WHEN UPPER(TRIM(pemuda.blood_type)) = "O" THEN "O"
                    ELSE "unknown"
                END as blood_group
            '), DB::raw('COUNT(pemuda.id) as total'))
            ->leftJoin('cabang', 'cabang.id', '=', 'pemuda.cabang_id');
        $self->scopeAndCustomFiltersRaw($builderBlood, $scope, $filters);
        $bloodRows = $builderBlood->groupBy('blood_group')->get();

        $bloodData = [
            'A'       => ['label' => 'Golongan A',  'code' => 'A',  'total' => 0, 'color' => '#dc3545', 'badge' => 'badge-danger',  'desc' => 'Dapat mendonor ke: A, AB', 'recipient' => 'Menerima dari: A, O'],
            'B'       => ['label' => 'Golongan B',  'code' => 'B',  'total' => 0, 'color' => '#007bff', 'badge' => 'badge-primary', 'desc' => 'Dapat mendonor ke: B, AB', 'recipient' => 'Menerima dari: B, O'],
            'AB'      => ['label' => 'Golongan AB', 'code' => 'AB', 'total' => 0, 'color' => '#6f42c1', 'badge' => 'badge-purple',  'desc' => 'Dapat mendonor ke: AB',    'recipient' => 'Resipien Universal (A, B, AB, O)'],
            'O'       => ['label' => 'Golongan O',  'code' => 'O',  'total' => 0, 'color' => '#28a745', 'badge' => 'badge-success', 'desc' => 'Donor Universal (ke semua)', 'recipient' => 'Menerima dari: O'],
            'unknown' => ['label' => 'Belum Tercatat / Tidak Tahu', 'code' => 'unknown', 'total' => 0, 'color' => '#adb5bd', 'badge' => 'badge-secondary', 'desc' => 'Perlu skrining/tes darah', 'recipient' => 'Belum ada data'],
        ];
        foreach ($bloodRows as $r) {
            $grp = $r->blood_group;
            if (isset($bloodData[$grp])) {
                $bloodData[$grp]['total'] = (int) $r->total;
            } else {
                $bloodData['unknown']['total'] += (int) $r->total;
            }
        }

        $totalWithBlood    = $bloodData['A']['total'] + $bloodData['B']['total'] + $bloodData['AB']['total'] + $bloodData['O']['total'];
        $totalUnknownBlood = $bloodData['unknown']['total'];
        $percentWithBlood  = $totalYouth > 0 ? round(($totalWithBlood / $totalYouth) * 100, 1) : 0;

        // 11. Status Pernikahan
        $builderMarital = DB::table('pemuda')
            ->select('pemuda.marital_status', DB::raw('COUNT(pemuda.id) as total'))
            ->leftJoin('cabang', 'cabang.id', '=', 'pemuda.cabang_id');
        $self->scopeAndCustomFiltersRaw($builderMarital, $scope, $filters);
        $maritalRows = $builderMarital->groupBy('pemuda.marital_status')->get();

        $maritalStats = [
            'belum_menikah' => ['label' => 'Belum Menikah', 'total' => 0, 'color' => '#3b82f6'],
            'sudah_menikah' => ['label' => 'Sudah Menikah', 'total' => 0, 'color' => '#10b981'],
            'duda'          => ['label' => 'Duda',          'total' => 0, 'color' => '#8b5cf6'],
            'janda'         => ['label' => 'Janda',         'total' => 0, 'color' => '#ec4899'],
        ];
        foreach ($maritalRows as $r) {
            $ms = $r->marital_status;
            if (isset($maritalStats[$ms])) {
                $maritalStats[$ms]['total'] = (int) $r->total;
            }
        }
        $maritalData = [
            'belum_menikah' => $maritalStats['belum_menikah']['total'],
            'sudah_menikah' => $maritalStats['sudah_menikah']['total'],
            'duda'          => $maritalStats['duda']['total'],
            'janda'         => $maritalStats['janda']['total'],
        ];

        // 12. Status Verifikasi API MTA
        $builderVerif = DB::table('pemuda')
            ->select('pemuda.status_verifikasi', DB::raw('COUNT(pemuda.id) as total'))
            ->leftJoin('cabang', 'cabang.id', '=', 'pemuda.cabang_id');
        $self->scopeAndCustomFiltersRaw($builderVerif, $scope, $filters);
        $verifRows = $builderVerif->groupBy('pemuda.status_verifikasi')->get();

        $verifStats = [
            'verified' => ['label' => 'Terverifikasi MTA', 'total' => 0, 'color' => '#10b981'],
            'pending'  => ['label' => 'Belum Terverifikasi', 'total' => 0, 'color' => '#f59e0b'],
        ];
        foreach ($verifRows as $r) {
            $sv = $r->status_verifikasi;
            if (isset($verifStats[$sv])) {
                $verifStats[$sv]['total'] = (int) $r->total;
            }
        }
        $verifData = [
            'verified' => $verifStats['verified']['total'],
            'pending'  => $verifStats['pending']['total'],
        ];

        // Aliases for convenient view consumption
        $orgData = [];
        foreach ($orgStats as $os) {
            $orgData[$os['name']] = (int) $os['total'];
        }

        $ageGroups = [];
        foreach ($ageData as $ag) {
            $ageGroups[$ag['label']] = (int) $ag['total'];
        }

        $bloodTypeData = [
            'A'       => $bloodData['A']['total'] ?? 0,
            'B'       => $bloodData['B']['total'] ?? 0,
            'AB'      => $bloodData['AB']['total'] ?? 0,
            'O'       => $bloodData['O']['total'] ?? 0,
            'unknown' => $bloodData['unknown']['total'] ?? 0,
        ];

        return [
            'totalYouth'         => $totalYouth,
            'genderData'         => $genderData,
            'totalWithOrg'       => $totalWithOrg,
            'totalWithoutOrg'    => $totalWithoutOrg,
            'orgStats'           => $orgStats,
            'orgData'            => $orgData,
            'eduLevelStats'      => $eduLevelStats,
            'educationStats'     => $eduLevelStats,
            'eduStatusData'      => $eduStatusData,
            'topSchools'         => $topSchools,
            'topMajors'          => $topMajors,
            'topSkills'          => $topSkills,
            'skillLevelData'     => $skillLevelData,
            'totalWithSkill'     => $totalWithSkill,
            'totalWithoutSkill'  => $totalWithoutSkill,
            'topInterests'       => $topInterests,
            'jobStats'           => $jobStats,
            'totalWirausaha'     => $totalWirausaha,
            'topBizFields'       => $topBizFields,
            'ageData'            => $ageData,
            'ageGroups'          => $ageGroups,
            'avgAge'             => $avgAge,
            'districtStats'      => $districtStats,
            'wilayahStats'       => $wilayahStats,
            'topCabangStats'     => $topCabangStats,
            'bloodData'          => $bloodData,
            'bloodTypeData'      => $bloodTypeData,
            'totalWithBlood'     => $totalWithBlood,
            'totalUnknownBlood'  => $totalUnknownBlood,
            'percentWithBlood'   => $percentWithBlood,
            'maritalStats'       => $maritalStats,
            'maritalData'        => $maritalData,
            'verifStats'         => $verifStats,
            'verifData'          => $verifData,
        ];
    }
}
