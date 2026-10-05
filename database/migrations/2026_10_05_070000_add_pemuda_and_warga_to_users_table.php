<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('pemuda_id')->nullable()->after('cabang_id');
            $table->string('mta_warga_uuid', 36)->nullable()->after('pemuda_id')->index();
            $table->string('sumber_data', 20)->default('manual')->after('mta_warga_uuid')->index();

            $table->foreign('pemuda_id')
                ->references('id')
                ->on('pemuda')
                ->onDelete('set null')
                ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['pemuda_id']);
            $table->dropColumn(['pemuda_id', 'mta_warga_uuid', 'sumber_data']);
        });
    }
};
