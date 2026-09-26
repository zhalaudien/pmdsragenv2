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
            $table->unsignedInteger('cabang_id')->nullable()->after('id')->index();
            $table->text('deskripsi')->nullable()->change();
            $table->string('target_peserta', 200)->nullable()->change();
            $table->string('penyelenggara', 200)->nullable()->change();

            $table->foreign('cabang_id')->references('id')->on('cabang')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kegiatan_perwakilan', function (Blueprint $table) {
            $table->dropForeign(['cabang_id']);
            $table->dropColumn('cabang_id');
        });
    }
};
