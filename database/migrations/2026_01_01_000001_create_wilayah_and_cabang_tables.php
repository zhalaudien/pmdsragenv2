<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('wilayah')) {
            Schema::create('wilayah', function (Blueprint $table) {
                $table->increments('id');
                $table->string('code', 50)->unique();
                $table->string('name', 100);
                $table->text('description')->nullable();
                $table->string('mta_uuid', 36)->nullable()->index('idx_wilayah_mta_uuid');
                $table->string('mta_code', 50)->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('cabang')) {
            Schema::create('cabang', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('wilayah_id');
                $table->string('code', 50)->nullable();
                $table->string('name', 100);
                $table->text('description')->nullable();
                $table->text('alamat')->nullable();
                $table->string('maps_url', 500)->nullable();
                $table->string('pimpinan_nama', 100)->nullable();
                $table->string('no_wa', 20)->nullable();
                $table->enum('has_gelombang', ['sudah', 'belum'])->default('belum')->index('idx_cabang_has_gelombang');
                $table->string('gelombang_hari', 100)->nullable();
                $table->string('gelombang_jam', 50)->nullable();
                $table->string('gelombang_ustadz', 150)->nullable();
                $table->string('mta_uuid', 36)->nullable()->index('idx_cabang_mta_uuid');
                $table->dateTime('mta_last_synced_at')->nullable();
                $table->timestamps();

                $table->foreign('wilayah_id')->references('id')->on('wilayah')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cabang');
        Schema::dropIfExists('wilayah');
    }
};
