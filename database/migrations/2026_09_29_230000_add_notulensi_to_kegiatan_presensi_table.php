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
        if (Schema::hasTable('kegiatan_presensi')) {
            Schema::table('kegiatan_presensi', function (Blueprint $table) {
                if (!Schema::hasColumn('kegiatan_presensi', 'notulensi')) {
                    $table->longText('notulensi')->nullable()->after('catatan')->comment('Notulensi / rangkuman materi kajian cabang');
                }
                if (!Schema::hasColumn('kegiatan_presensi', 'notulis')) {
                    $table->string('notulis', 150)->nullable()->after('notulensi')->comment('Nama notulis / pencatat notulensi');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('kegiatan_presensi')) {
            Schema::table('kegiatan_presensi', function (Blueprint $table) {
                if (Schema::hasColumn('kegiatan_presensi', 'notulis')) {
                    $table->dropColumn('notulis');
                }
                if (Schema::hasColumn('kegiatan_presensi', 'notulensi')) {
                    $table->dropColumn('notulensi');
                }
            });
        }
    }
};
