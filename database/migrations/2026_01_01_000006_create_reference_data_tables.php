<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('education_levels')) {
            Schema::create('education_levels', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 50)->unique();
                $table->string('description', 255)->nullable();
            });
        }

        if (!Schema::hasTable('job_statuses')) {
            Schema::create('job_statuses', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 50)->unique();
                $table->string('description', 255)->nullable();
            });
        }

        if (!Schema::hasTable('skills')) {
            Schema::create('skills', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 100)->unique();
                $table->text('description')->nullable();
            });
        }

        if (!Schema::hasTable('interests')) {
            Schema::create('interests', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 100)->unique();
                $table->text('description')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('interests');
        Schema::dropIfExists('skills');
        Schema::dropIfExists('job_statuses');
        Schema::dropIfExists('education_levels');
    }
};
