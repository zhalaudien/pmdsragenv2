<?php

namespace Database\Seeders;

use App\Models\ApiSetting;
use Illuminate\Database\Seeder;

class ApiSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ApiSetting::seedDefaults();
    }
}
