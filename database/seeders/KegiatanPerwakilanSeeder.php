<?php

namespace Database\Seeders;

use App\Models\KegiatanPerwakilan;
use Illuminate\Database\Seeder;

class KegiatanPerwakilanSeeder extends Seeder
{
    public function run(): void
    {
        KegiatanPerwakilan::seedDefaults(true);
    }
}
