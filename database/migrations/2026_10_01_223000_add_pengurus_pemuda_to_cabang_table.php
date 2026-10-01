<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cabang', function (Blueprint $table) {
            $table->string('ketua_pemuda', 100)->nullable()->after('gelombang_ustadz');
            $table->string('sekretaris_pemuda', 100)->nullable()->after('ketua_pemuda');
            $table->string('bendahara_pemuda', 100)->nullable()->after('sekretaris_pemuda');
            $table->string('no_wa_pemuda', 20)->nullable()->after('bendahara_pemuda');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cabang', function (Blueprint $table) {
            $table->dropColumn([
                'ketua_pemuda',
                'sekretaris_pemuda',
                'bendahara_pemuda',
                'no_wa_pemuda',
            ]);
        });
    }
};
