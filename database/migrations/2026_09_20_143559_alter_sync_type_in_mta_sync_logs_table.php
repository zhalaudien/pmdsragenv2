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
        Schema::table('mta_sync_logs', function (Blueprint $table) {
            $table->string('sync_type', 50)->default('warga')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mta_sync_logs', function (Blueprint $table) {
            $table->enum('sync_type', ['perwakilan', 'cabang', 'warga', 'check'])->default('warga')->change();
        });
    }
};
