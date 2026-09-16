<?php

namespace Database\Seeders;

use App\Models\HomepageSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HomepageSettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = HomepageSetting::getDefaults();

        foreach ($defaults as $key => $item) {
            DB::table('homepage_settings')->updateOrInsert(
                ['key' => $key],
                [
                    'group'      => $item['group'],
                    'value'      => $item['value'],
                    'type'       => $item['type'],
                    'label'      => $item['label'],
                    'updated_at' => now(),
                ]
            );
        }
    }
}
