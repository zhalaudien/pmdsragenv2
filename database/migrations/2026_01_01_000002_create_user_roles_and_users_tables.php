<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('user_roles')) {
            Schema::create('user_roles', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 50);
                $table->string('description', 255)->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 100);
                $table->string('email', 100)->unique();
                $table->string('username', 50)->unique();
                $table->string('password', 255);
                $table->unsignedInteger('role_id');
                $table->unsignedInteger('wilayah_id')->nullable();
                $table->unsignedInteger('cabang_id')->nullable();
                $table->dateTime('last_login')->nullable();
                $table->boolean('status')->default(true);
                $table->rememberToken();
                $table->timestamps();

                $table->foreign('role_id')->references('id')->on('user_roles')->onDelete('cascade');
                $table->foreign('wilayah_id')->references('id')->on('wilayah')->onDelete('cascade')->onUpdate('set null');
                $table->foreign('cabang_id')->references('id')->on('cabang')->onDelete('cascade')->onUpdate('set null');
            });
        }

        if (!Schema::hasTable('password_reset_tokens')) {
            Schema::create('password_reset_tokens', function (Blueprint $table) {
                $table->string('email')->primary();
                $table->string('token');
                $table->timestamp('created_at')->nullable();
            });
        }

        if (!Schema::hasTable('sessions')) {
            Schema::create('sessions', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->foreignId('user_id')->nullable()->index();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->longText('payload');
                $table->integer('last_activity')->index();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
        Schema::dropIfExists('user_roles');
    }
};
