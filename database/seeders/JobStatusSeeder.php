<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JobStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            [
                'id'          => 1,
                'name'        => 'Belum / Tidak Bekerja',
                'description' => 'Belum atau sedang tidak bekerja',
            ],
            [
                'id'          => 2,
                'name'        => 'Pelajar / Mahasiswa',
                'description' => 'Sedang menempuh pendidikan sekolah atau perkuliahan',
            ],
            [
                'id'          => 3,
                'name'        => 'Karyawan Swasta',
                'description' => 'Bekerja sebagai pegawai di perusahaan swasta',
            ],
            [
                'id'          => 4,
                'name'        => 'Pegawai Negeri / ASN / PPPK',
                'description' => 'Aparatur Sipil Negara, PNS, PPPK, atau instansi pemerintahan',
            ],
            [
                'id'          => 5,
                'name'        => 'Wirausaha / Pemilik Usaha',
                'description' => 'Memiliki atau mengelola bisnis/usaha mandiri',
            ],
            [
                'id'          => 6,
                'name'        => 'Freelancer / Pekerja Lepas',
                'description' => 'Pekerja profesional mandiri/lepas',
            ],
            [
                'id'          => 7,
                'name'        => 'Petani / Peternak',
                'description' => 'Bekerja di sektor pertanian, perkebunan, atau peternakan',
            ],
            [
                'id'          => 8,
                'name'        => 'Lainnya',
                'description' => 'Bidang pekerjaan atau profesi lainnya',
            ],
        ];

        foreach ($statuses as $item) {
            DB::table('job_statuses')->updateOrInsert(
                ['id' => $item['id']],
                [
                    'name'        => $item['name'],
                    'description' => $item['description'],
                ]
            );
        }
    }
}
