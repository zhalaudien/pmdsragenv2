<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $existing = DB::table('user_roles')->where('name', 'koordinator_gdm')->first();

        if (!$existing) {
            DB::table('user_roles')->updateOrInsert(
                ['id' => 7],
                [
                    'name'        => 'koordinator_gdm',
                    'description' => 'Koordinator Guru Daerah Muda (GDM) untuk manajemen GDM dan penugasan kajian cabang',
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('user_roles')->where('name', 'koordinator_gdm')->delete();
    }
};
