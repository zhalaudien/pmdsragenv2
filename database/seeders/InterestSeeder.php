<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InterestSeeder extends Seeder
{
    public function run(): void
    {
        $interests = [
            [
                'id'          => 1,
                'name'        => 'Olahraga & Kebugaran',
                'description' => 'Aktivitas kebugaran, turnamen olahraga, dan atletik',
            ],
            [
                'id'          => 2,
                'name'        => 'Seni Musik & Tari',
                'description' => 'Bermusik, bernyanyi, tari tradisional dan kreasi',
            ],
            [
                'id'          => 3,
                'name'        => 'Seni Rupa & Kriya',
                'description' => 'Menggambar, melukis, kriya tangan, dan kerajinan seni',
            ],
            [
                'id'          => 4,
                'name'        => 'Teknologi & Robotika',
                'description' => 'Inovasi perangkat digital, elektronika, AI, dan coding',
            ],
            [
                'id'          => 5,
                'name'        => 'Kewirausahaan & UMKM',
                'description' => 'Pengembangan bisnis, startup, dan inovasi produk usaha',
            ],
            [
                'id'          => 6,
                'name'        => 'Aksi Relawan & Sosial',
                'description' => 'Kegiatan sosial kemanusiaan, bakti sosial, dan relawan bencana',
            ],
            [
                'id'          => 7,
                'name'        => 'Kelestarian Lingkungan',
                'description' => 'Penghijauan lingkungan hidup, daur ulang sampah, dan eco-living',
            ],
            [
                'id'          => 8,
                'name'        => 'Literasi & Buku',
                'description' => 'Membaca, penulisan karya literasi, dan bedah buku',
            ],
            [
                'id'          => 9,
                'name'        => 'Kajian & Keagamaan',
                'description' => 'Kajian keislaman, tahsin, dakwah, dan pemahaman agama',
            ],
            [
                'id'          => 10,
                'name'        => 'Pariwisata & Budaya Lokal',
                'description' => 'Eksplorasi destinasi wisata, sejarah daerah, dan budaya lokal',
            ],
            [
                'id'          => 11,
                'name'        => 'E-Sport & Gaming',
                'description' => 'Kompetisi game elektronik kompetitif dan strategi game',
            ],
            [
                'id'          => 12,
                'name'        => 'Kepemimpinan & Organisasi',
                'description' => 'Pelatihan kepemimpinan, public policy, dan dinamika organisasi',
            ],
        ];

        foreach ($interests as $item) {
            DB::table('interests')->updateOrInsert(
                ['id' => $item['id']],
                [
                    'name'        => $item['name'],
                    'description' => $item['description'],
                ]
            );
        }
    }
}
