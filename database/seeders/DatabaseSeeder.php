<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserRoleSeeder::class,
            WilayahSeeder::class,
            CabangSeeder::class,
            RegionalSeeder::class,
            EducationLevelSeeder::class,
            JobStatusSeeder::class,
            SkillSeeder::class,
            InterestSeeder::class,
            HomepageSettingSeeder::class,
            UserSeeder::class,
        ]);
    }
}
