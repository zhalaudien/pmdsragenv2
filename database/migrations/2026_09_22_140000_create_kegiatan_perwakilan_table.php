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
        Schema::dropIfExists('kegiatan_perwakilan');

        Schema::create('kegiatan_perwakilan', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nama_kegiatan', 200);
            $table->string('kategori', 50)->default('Kajian Akbar')->index();
            $table->date('tanggal')->index();
            $table->string('hari_tanggal', 100)->nullable();
            $table->string('jam', 100)->default('08.30 - Selesai');
            $table->string('lokasi', 255);
            $table->text('alamat_detail')->nullable();
            $table->string('pemateri', 200)->nullable();
            $table->string('target_peserta', 200)->default('Seluruh Pemuda & Pemudi 70 Cabang se-Sragen');
            $table->string('penyelenggara', 200)->default('Pengurus Pemuda MTA Perwakilan Sragen');
            $table->text('deskripsi');
            $table->text('catatan_ketentuan')->nullable();
            $table->string('narahubung', 150)->nullable();
            $table->enum('status', ['Akan Datang', 'Segera', 'Berlangsung', 'Selesai'])->default('Akan Datang')->index();
            $table->boolean('is_active')->default(true)->index();
            $table->integer('urutan')->default(0);
            $table->unsignedInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kegiatan_perwakilan');
    }
};
