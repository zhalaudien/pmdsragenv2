<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('homepage_settings')) {
            Schema::create('homepage_settings', function (Blueprint $table) {
                $table->increments('id');
                $table->string('group', 50)->default('general')->index();
                $table->string('key', 100)->unique();
                $table->longText('value')->nullable();
                $table->string('type', 20)->default('text');
                $table->string('label', 150)->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('homepage_settings');
    }
};
