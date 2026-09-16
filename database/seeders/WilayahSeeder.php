<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WilayahSeeder extends Seeder
{
    public function run(): void
    {
        $wilayah = [
            [
                'id'          => 1,
                'code'        => 'W01',
                'name'        => 'Wilayah 1',
                'description' => 'Wilayah 1 (Gesi, Jenar, Mondokan, Sukodono, Sumberlawang, Tangen, Tanon)',
            ],
            [
                'id'          => 2,
                'code'        => 'W02',
                'name'        => 'Wilayah 2',
                'description' => 'Wilayah 2 (Gemolong, Kalijambe, Miri, Plupuh)',
            ],
            [
                'id'          => 3,
                'code'        => 'W03',
                'name'        => 'Wilayah 3',
                'description' => 'Wilayah 3 (Karangmalang, Masaran, Sambungmacan, Sidoharjo, Sragen)',
            ],
            [
                'id'          => 4,
                'code'        => 'W04',
                'name'        => 'Wilayah 4',
                'description' => 'Wilayah 4 (Gondang, Kedawung, Ngrampal, Sambirejo)',
            ],
        ];

        foreach ($wilayah as $item) {
            DB::table('wilayah')->updateOrInsert(
                ['id' => $item['id']],
                [
                    'code'        => $item['code'],
                    'name'        => $item['name'],
                    'description' => $item['description'],
                    'updated_at'  => now(),
                ]
            );
        }
    }
}
