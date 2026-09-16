<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('mta_sync_logs')) {
            Schema::create('mta_sync_logs', function (Blueprint $table) {
                $table->increments('id');
                $table->enum('sync_type', ['perwakilan', 'cabang', 'warga', 'check'])->default('warga')->index();
                $table->enum('status', ['success', 'failed'])->default('success')->index();
                $table->unsignedInteger('total_records')->default(0);
                $table->text('message')->nullable();
                $table->unsignedInteger('created_by')->nullable();
                $table->dateTime('created_at')->useCurrent();

                $table->foreign('created_by')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            });
        }

        if (!Schema::hasTable('mta_sync_queue')) {
            Schema::create('mta_sync_queue', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('pemuda_id');
                $table->unsignedInteger('cabang_id');
                $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending')->index();
                $table->enum('result', ['verified', 'pending', 'error'])->default('pending');
                $table->string('message', 255)->nullable();
                $table->string('mta_warga_uuid', 36)->nullable();
                $table->unsignedInteger('attempts')->default(0);
                $table->unsignedInteger('created_by')->nullable();
                $table->dateTime('created_at')->useCurrent()->index();
                $table->dateTime('updated_at')->useCurrent();
                $table->dateTime('processed_at')->nullable();

                $table->foreign('pemuda_id')->references('id')->on('pemuda')->onDelete('cascade')->onUpdate('cascade');
                $table->foreign('cabang_id')->references('id')->on('cabang')->onDelete('cascade')->onUpdate('cascade');
                $table->foreign('created_by')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mta_sync_queue');
        Schema::dropIfExists('mta_sync_logs');
    }
};
