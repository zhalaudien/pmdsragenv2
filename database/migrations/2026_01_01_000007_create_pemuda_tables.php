<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('pemuda')) {
            Schema::create('pemuda', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('cabang_id');
                $table->string('registration_number', 30)->unique();
                $table->string('name', 150)->index();
                $table->enum('gender', ['L', 'P'])->index();
                $table->enum('marital_status', ['belum_menikah', 'sudah_menikah', 'janda', 'duda'])->default('belum_menikah')->index('idx_pemuda_marital_status');
                $table->string('birth_place', 100)->nullable();
                $table->date('birth_date')->nullable();
                $table->string('blood_type', 10)->nullable();
                $table->string('phone', 20)->nullable();
                $table->string('email', 100)->nullable();
                $table->enum('status_verifikasi', ['pending', 'verified', 'rejected'])->default('pending')->index();
                $table->enum('status_data', ['active', 'archived'])->default('active')->index();
                $table->string('mta_warga_uuid', 36)->nullable()->index('idx_pemuda_mta_warga_uuid');
                $table->string('mta_status_warga', 50)->nullable();
                $table->string('mta_ayah_uuid', 36)->nullable();
                $table->string('mta_ibu_uuid', 36)->nullable();
                $table->string('mta_foto_url', 255)->nullable();
                $table->string('foto', 255)->nullable();
                $table->dateTime('mta_synced_at')->nullable();
                $table->unsignedInteger('created_by')->nullable();
                $table->timestamps();

                $table->foreign('cabang_id')->references('id')->on('cabang')->onDelete('cascade');
                $table->foreign('created_by')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            });
        }

        if (!Schema::hasTable('alamat')) {
            Schema::create('alamat', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('pemuda_id')->unique();
                $table->unsignedInteger('province_id');
                $table->unsignedInteger('regency_id');
                $table->unsignedInteger('district_id');
                $table->unsignedInteger('village_id');
                $table->string('dusun', 100)->nullable();
                $table->string('rt', 5)->nullable();
                $table->string('rw', 5)->nullable();
                $table->text('address_detail')->nullable();
                $table->timestamps();

                $table->foreign('pemuda_id')->references('id')->on('pemuda')->onDelete('cascade')->onUpdate('cascade');
                $table->foreign('province_id')->references('id')->on('provinces')->onDelete('cascade');
                $table->foreign('regency_id')->references('id')->on('regencies')->onDelete('cascade');
                $table->foreign('district_id')->references('id')->on('districts')->onDelete('cascade');
                $table->foreign('village_id')->references('id')->on('villages')->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('pendidikan')) {
            Schema::create('pendidikan', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('pemuda_id')->unique();
                $table->unsignedInteger('education_level_id');
                $table->string('school_name', 150)->nullable();
                $table->string('major', 150)->nullable();
                $table->enum('education_status', ['sedang_sekolah', 'lulus', 'putus_sekolah'])->default('lulus');
                $table->year('graduation_year')->nullable();
                $table->timestamps();

                $table->foreign('pemuda_id')->references('id')->on('pemuda')->onDelete('cascade')->onUpdate('cascade');
                $table->foreign('education_level_id')->references('id')->on('education_levels')->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('pekerjaan')) {
            Schema::create('pekerjaan', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('pemuda_id')->unique();
                $table->unsignedInteger('job_status_id');
                $table->string('job_title', 150)->nullable();
                $table->string('company_name', 150)->nullable();
                $table->string('business_field', 150)->nullable();
                $table->string('business_name', 150)->nullable();
                $table->text('business_address')->nullable();
                $table->string('business_contact', 50)->nullable();
                $table->string('business_social', 255)->nullable();
                $table->timestamps();

                $table->foreign('pemuda_id')->references('id')->on('pemuda')->onDelete('cascade')->onUpdate('cascade');
                $table->foreign('job_status_id')->references('id')->on('job_statuses')->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('organisasi')) {
            Schema::create('organisasi', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('pemuda_id')->index();
                $table->string('organization_name', 150);
                $table->text('description')->nullable();
                $table->timestamps();

                $table->foreign('pemuda_id')->references('id')->on('pemuda')->onDelete('cascade')->onUpdate('cascade');
            });
        }

        if (!Schema::hasTable('pemuda_skills')) {
            Schema::create('pemuda_skills', function (Blueprint $table) {
                $table->unsignedInteger('pemuda_id');
                $table->unsignedInteger('skill_id');
                $table->enum('level', ['pemula', 'menengah', 'mahir'])->default('pemula');
                $table->primary(['pemuda_id', 'skill_id']);

                $table->foreign('pemuda_id')->references('id')->on('pemuda')->onDelete('cascade')->onUpdate('cascade');
                $table->foreign('skill_id')->references('id')->on('skills')->onDelete('cascade')->onUpdate('cascade');
            });
        }

        if (!Schema::hasTable('pemuda_interests')) {
            Schema::create('pemuda_interests', function (Blueprint $table) {
                $table->unsignedInteger('pemuda_id');
                $table->unsignedInteger('interest_id');
                $table->primary(['pemuda_id', 'interest_id']);

                $table->foreign('pemuda_id')->references('id')->on('pemuda')->onDelete('cascade')->onUpdate('cascade');
                $table->foreign('interest_id')->references('id')->on('interests')->onDelete('cascade')->onUpdate('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pemuda_interests');
        Schema::dropIfExists('pemuda_skills');
        Schema::dropIfExists('organisasi');
        Schema::dropIfExists('pekerjaan');
        Schema::dropIfExists('pendidikan');
        Schema::dropIfExists('alamat');
        Schema::dropIfExists('pemuda');
    }
};
