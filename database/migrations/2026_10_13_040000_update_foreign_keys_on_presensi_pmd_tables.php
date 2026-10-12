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
                $table->dropForeign('fk_kegiatan_creator');
            });

            Schema::table('kegiatan_presensi', function (Blueprint $table) {
                $table->unsignedInteger('created_by')->nullable()->comment('User ID pembuat')->change();
                $table->foreign('created_by', 'fk_kegiatan_creator')
                    ->references('id')->on('users')
                    ->onDelete('set null');
            });
        }

        if (Schema::hasTable('presensi_detail')) {
            Schema::table('presensi_detail', function (Blueprint $table) {
                $table->dropForeign('fk_presensi_user');
            });

            Schema::table('presensi_detail', function (Blueprint $table) {
                $table->unsignedInteger('created_by')->nullable()->comment('User ID pencatat')->change();
                $table->foreign('created_by', 'fk_presensi_user')
                    ->references('id')->on('users')
                    ->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('presensi_detail')) {
            Schema::table('presensi_detail', function (Blueprint $table) {
                $table->dropForeign('fk_presensi_user');
            });

            Schema::table('presensi_detail', function (Blueprint $table) {
                $table->unsignedInteger('created_by')->nullable(false)->change();
                $table->foreign('created_by', 'fk_presensi_user')
                    ->references('id')->on('users')
                    ->onDelete('restrict');
            });
        }

        if (Schema::hasTable('kegiatan_presensi')) {
            Schema::table('kegiatan_presensi', function (Blueprint $table) {
                $table->dropForeign('fk_kegiatan_creator');
            });

            Schema::table('kegiatan_presensi', function (Blueprint $table) {
                $table->unsignedInteger('created_by')->nullable(false)->change();
                $table->foreign('created_by', 'fk_kegiatan_creator')
                    ->references('id')->on('users')
                    ->onDelete('restrict');
            });
        }
    }
};
