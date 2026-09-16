<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $defaultPassword = Hash::make('admin123');

        $users = [
            [
                'id'         => 1,
                'name'       => 'Super Administrator',
                'email'      => 'superadmin@pmdsragen.org',
                'username'   => 'superadmin',
                'password'   => $defaultPassword,
                'role_id'    => 1,
                'wilayah_id' => null,
                'cabang_id'  => null,
                'status'     => 1,
            ],
            [
                'id'         => 2,
                'name'       => 'Admin Wilayah 1',
                'email'      => 'admin.w1@pmdsragen.org',
                'username'   => 'admin_w1',
                'password'   => $defaultPassword,
                'role_id'    => 2,
                'wilayah_id' => 1,
                'cabang_id'  => null,
                'status'     => 1,
            ],
            [
                'id'         => 3,
                'name'       => 'Admin Cabang Gesi',
                'email'      => 'admin.gesi@pmdsragen.org',
                'username'   => 'admin_gesi',
                'password'   => $defaultPassword,
                'role_id'    => 3,
                'wilayah_id' => 1,
                'cabang_id'  => 1,
                'status'     => 1,
            ],
            [
                'id'         => 4,
                'name'       => 'Admin Pemuda Sragen',
                'email'      => 'admin.pemuda@pmdsragen.org',
                'username'   => 'admin_pemuda',
                'password'   => $defaultPassword,
                'role_id'    => 4,
                'wilayah_id' => null,
                'cabang_id'  => null,
                'status'     => 1,
            ],
            [
                'id'         => 5,
                'name'       => 'Admin Pemudi Sragen',
                'email'      => 'admin.pemudi@pmdsragen.org',
                'username'   => 'admin_pemudi',
                'password'   => $defaultPassword,
                'role_id'    => 5,
                'wilayah_id' => null,
                'cabang_id'  => null,
                'status'     => 1,
            ],
            [
                'id'         => 6,
                'name'       => 'Admin Wilayah 1 Pemuda',
                'email'      => 'admin.w1pemuda@pmdsragen.org',
                'username'   => 'admin_w1_pemuda',
                'password'   => $defaultPassword,
                'role_id'    => 6,
                'wilayah_id' => 1,
                'cabang_id'  => null,
                'status'     => 1,
            ],
        ];

        foreach ($users as $user) {
            $existing = DB::table('users')->where('id', $user['id'])->orWhere('username', $user['username'])->first();
            if (!$existing) {
                DB::table('users')->insert(array_merge($user, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }
    }
}
