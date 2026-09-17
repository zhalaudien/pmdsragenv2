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
        if (Schema::hasColumn('pemuda', 'nik')) {
            Schema::table('pemuda', function (Blueprint $table) {
                $table->dropColumn('nik');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasColumn('pemuda', 'nik')) {
            Schema::table('pemuda', function (Blueprint $table) {
                $table->string('nik', 20)->nullable()->after('name');
            });
        }
    }
};
