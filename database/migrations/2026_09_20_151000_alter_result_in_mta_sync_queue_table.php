<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('mta_sync_queue')) {
            // Ubah result dari ENUM('verified','pending','error') menjadi VARCHAR(50)
            DB::statement("ALTER TABLE mta_sync_queue MODIFY COLUMN result VARCHAR(50) NOT NULL DEFAULT 'pending'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('mta_sync_queue')) {
            DB::statement("ALTER TABLE mta_sync_queue MODIFY COLUMN result ENUM('verified', 'pending', 'error') NOT NULL DEFAULT 'pending'");
        }
    }
};
