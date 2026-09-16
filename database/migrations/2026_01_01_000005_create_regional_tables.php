<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('provinces')) {
            Schema::create('provinces', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 100)->unique();
            });
        }

        if (!Schema::hasTable('regencies')) {
            Schema::create('regencies', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('province_id');
                $table->string('name', 100);
                $table->unique(['province_id', 'name']);
                $table->foreign('province_id')->references('id')->on('provinces')->onUpdate('cascade');
            });
        }

        if (!Schema::hasTable('districts')) {
            Schema::create('districts', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('regency_id');
                $table->string('name', 100);
                $table->unique(['regency_id', 'name']);
                $table->foreign('regency_id')->references('id')->on('regencies')->onUpdate('cascade');
            });
        }

        if (!Schema::hasTable('villages')) {
            Schema::create('villages', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('district_id');
                $table->string('name', 100);
                $table->unique(['district_id', 'name']);
                $table->foreign('district_id')->references('id')->on('districts')->onUpdate('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('villages');
        Schema::dropIfExists('districts');
        Schema::dropIfExists('regencies');
        Schema::dropIfExists('provinces');
    }
};
