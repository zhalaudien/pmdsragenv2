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
        if (!Schema::hasTable('api_settings')) {
            Schema::create('api_settings', function (Blueprint $table) {
                $table->increments('id');
                $table->string('group', 50)->default('general')->index();
                $table->string('key', 100)->unique();
                $table->text('value')->nullable();
                $table->string('type', 50)->default('text');
                $table->string('label', 150);
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_settings');
    }
};
