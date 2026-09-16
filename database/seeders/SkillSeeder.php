<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SkillSeeder extends Seeder
{
    public function run(): void
    {
        $skills = [
            [
                'id'          => 1,
                'name'        => 'Desain Grafis & Multimedia',
                'description' => 'Kemampuan desain visual, editing foto, video, dan grafis digital',
            ],
            [
                'id'          => 2,
                'name'        => 'Pemrograman & IT (Web/Mobile)',
                'description' => 'Pengembangan aplikasi web, mobile, software, dan infrastruktur IT',
            ],
            [
                'id'          => 3,
                'name'        => 'Digital Marketing & Social Media',
                'description' => 'Pemasaran online, optimasi SEO, media sosial, dan content creation',
            ],
            [
                'id'          => 4,
                'name'        => 'Public Speaking & Komunikasi',
                'description' => 'Kemampuan berbicara di depan umum, MC, dan komunikasi publik',
            ],
            [
                'id'          => 5,
                'name'        => 'Fotografi & Videografi',
                'description' => 'Pengambilan foto, video production, dan sinematografi',
            ],
            [
                'id'          => 6,
                'name'        => 'Pertanian Modern & Hidroponik',
                'description' => 'Teknik budidaya pertanian modern, hidroponik, dan agribisnis',
            ],
            [
                'id'          => 7,
                'name'        => 'Tata Boga & Kuliner',
                'description' => 'Keahlian memasak, bakery, pastry, dan manajemen kuliner',
            ],
            [
                'id'          => 8,
                'name'        => 'Menjahit & Tata Busana',
                'description' => 'Pembuatan pola pakaian, menjahit busana, dan desain fashion',
            ],
            [
                'id'          => 9,
                'name'        => 'Teknik Otomotif & Mesin',
                'description' => 'Perawatan dan perbaikan mesin sepeda motor, mobil, dan permesinan',
            ],
            [
                'id'          => 10,
                'name'        => 'Administrasi & Pembukuan',
                'description' => 'Tata kelola dokumen, akuntansi dasar, dan pembukuan keuangan',
            ],
            [
                'id'          => 11,
                'name'        => 'Bahasa Asing (Inggris/Lainnya)',
                'description' => 'Penguasaan bahasa asing lisan maupun tulisan',
            ],
            [
                'id'          => 12,
                'name'        => 'Kepemimpinan & Manajemen Tim',
                'description' => 'Kepemimpinan organisasi, koordinasi program, dan manajemen tim',
            ],
        ];

        foreach ($skills as $item) {
            DB::table('skills')->updateOrInsert(
                ['id' => $item['id']],
                [
                    'name'        => $item['name'],
                    'description' => $item['description'],
                ]
            );
        }
    }
}
