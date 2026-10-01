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
        if (!Schema::hasTable('guru_daerah_muda')) {
            Schema::create('guru_daerah_muda', function (Blueprint $table) {
                $table->increments('id');
                $table->string('nama', 150)->index('idx_gdm_nama');
                $table->string('tempat_lahir', 100)->nullable();
                $table->date('tanggal_lahir')->nullable();
                $table->unsignedInteger('cabang_id')->nullable()->index('idx_gdm_cabang_id');
                $table->text('alamat')->nullable();
                $table->string('no_wa', 25)->nullable();
                $table->enum('status', ['aktif', 'nonaktif'])->default('aktif')->index('idx_gdm_status');
                $table->enum('sumber_data', ['pemuda', 'warga', 'manual'])->default('manual')->index('idx_gdm_sumber');
                $table->unsignedInteger('pemuda_id')->nullable()->index('idx_gdm_pemuda_id');
                $table->string('mta_warga_uuid', 36)->nullable()->index('idx_gdm_mta_warga_uuid');
                $table->text('catatan')->nullable();
                $table->timestamps();

                $table->foreign('cabang_id')->references('id')->on('cabang')->nullOnDelete();
                $table->foreign('pemuda_id')->references('id')->on('pemuda')->nullOnDelete();
            });
        }

        if (!Schema::hasTable('gdm_penugasan')) {
            Schema::create('gdm_penugasan', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('gdm_id')->index('idx_penugasan_gdm_id');
                $table->smallInteger('tahun')->index('idx_penugasan_tahun');
                $table->unsignedInteger('cabang_id')->index('idx_penugasan_cabang_id'); // Cabang tempat penugasan kajian
                $table->string('hari_kajian', 50)->nullable();
                $table->string('jam_kajian', 50)->nullable();
                $table->enum('status', ['aktif', 'selesai', 'ditarik'])->default('aktif')->index('idx_penugasan_status');
                $table->text('keterangan')->nullable();
                $table->timestamps();

                $table->foreign('gdm_id')->references('id')->on('guru_daerah_muda')->cascadeOnDelete();
                $table->foreign('cabang_id')->references('id')->on('cabang')->cascadeOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gdm_penugasan');
        Schema::dropIfExists('guru_daerah_muda');
    }
};
