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
        Schema::table('kegiatan_perwakilan', function (Blueprint $table) {
            $table->string('flyer', 255)->nullable()->after('catatan_ketentuan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kegiatan_perwakilan', function (Blueprint $table) {
            $table->dropColumn('flyer');
        });
    }
};
