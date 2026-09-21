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
        if (!Schema::hasTable('kegiatan_presensi')) {
            Schema::create('kegiatan_presensi', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('cabang_id');
                $table->string('nama_kegiatan', 150)->comment('Contoh: Pengajian Rutin Pemuda, Musyawarah Cabang');
                $table->date('tanggal');
                $table->time('jam_mulai')->nullable();
                $table->time('jam_selesai')->nullable();
                $table->string('lokasi', 200)->nullable()->comment('Contoh: Masjid Al-Huda / Rumah Sdr. Fulan');
                $table->string('pemateri', 150)->nullable()->comment('Nama Ustadz / Pembicara');
                $table->enum('target_peserta', ['semua', 'pemuda', 'pemudi'])->default('semua');
                $table->enum('status', ['draft', 'berlangsung', 'selesai'])->default('berlangsung');
                $table->text('catatan')->nullable();
                $table->unsignedInteger('created_by')->comment('User ID sekretaris pembuat');
                $table->timestamps();

                $table->index(['cabang_id', 'tanggal'], 'idx_kegiatan_cabang_tanggal');
                $table->index('status', 'idx_kegiatan_status');

                $table->foreign('cabang_id', 'fk_kegiatan_cabang')
                    ->references('id')->on('cabang')
                    ->onDelete('cascade');

                $table->foreign('created_by', 'fk_kegiatan_creator')
                    ->references('id')->on('users')
                    ->onDelete('restrict');
            });
        }

        if (!Schema::hasTable('presensi_detail')) {
            Schema::create('presensi_detail', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedInteger('kegiatan_presensi_id');
                $table->unsignedInteger('pemuda_id');
                $table->enum('status_kehadiran', ['hadir', 'izin', 'sakit', 'alpa'])->default('hadir');
                $table->text('keterangan')->nullable()->comment('Alasan jika izin atau sakit');
                $table->dateTime('waktu_presensi')->nullable()->comment('Waktu saat dicentang');
                $table->string('device_info', 100)->nullable()->comment('Identitas device pencatat');
                $table->unsignedInteger('created_by');
                $table->timestamps();

                $table->unique(['kegiatan_presensi_id', 'pemuda_id'], 'uk_kegiatan_pemuda');
                $table->index('status_kehadiran', 'idx_presensi_status');

                $table->foreign('kegiatan_presensi_id', 'fk_presensi_kegiatan')
                    ->references('id')->on('kegiatan_presensi')
                    ->onDelete('cascade');

                $table->foreign('pemuda_id', 'fk_presensi_pemuda')
                    ->references('id')->on('pemuda')
                    ->onDelete('cascade');

                $table->foreign('created_by', 'fk_presensi_user')
                    ->references('id')->on('users')
                    ->onDelete('restrict');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presensi_detail');
        Schema::dropIfExists('kegiatan_presensi');
    }
};
