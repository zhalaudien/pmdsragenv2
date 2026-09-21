<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CabangPemudaController;
use App\Http\Controllers\Api\V1\KegiatanController;
use App\Http\Controllers\Api\V1\PresensiController;
use App\Http\Middleware\CheckApiMaintenance;
use App\Http\Middleware\EnforceCabangScope;

/*
|--------------------------------------------------------------------------
| API Routes - Presensi PMD Mobile (Flutter Android)
| Sistem Terintegrasi Pendataan & Presensi Pemuda MTA Perwakilan Sragen
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    // 1. Endpoint Publik & Konfigurasi Aplikasi Mobile
    Route::get('config', [AuthController::class, 'config'])->name('api.v1.config');
    Route::post('auth/login', [AuthController::class, 'login'])
        ->middleware(CheckApiMaintenance::class)
        ->name('api.v1.auth.login');

    // 2. Endpoint Terproteksi (Bearer Token Laravel Sanctum)
    Route::middleware(['auth:sanctum', CheckApiMaintenance::class])->group(function () {
        // Otentikasi & Profil Sesi
        Route::post('auth/logout', [AuthController::class, 'logout'])->name('api.v1.auth.logout');
        Route::get('auth/me', [AuthController::class, 'me'])->name('api.v1.auth.me');

        // Scope Cabang (Isolasi data antar cabang server-side)
        Route::middleware([EnforceCabangScope::class])->group(function () {
            // Data Anggota Pemuda di Cabang
            Route::get('cabang/pemuda', [CabangPemudaController::class, 'index'])->name('api.v1.cabang.pemuda');

            // Manajemen Sesi Kegiatan Presensi
            Route::get('kegiatan', [KegiatanController::class, 'index'])->name('api.v1.kegiatan.index');
            Route::post('kegiatan', [KegiatanController::class, 'store'])->name('api.v1.kegiatan.store');
            Route::get('kegiatan/{id}', [KegiatanController::class, 'show'])->name('api.v1.kegiatan.show');
            Route::put('kegiatan/{id}/status', [KegiatanController::class, 'updateStatus'])->name('api.v1.kegiatan.status');
            Route::get('kegiatan/{id}/rekap', [KegiatanController::class, 'rekap'])->name('api.v1.kegiatan.rekap');

            // Pencatatan Status Presensi (Realtime Single & Offline Bulk Sync)
            Route::post('kegiatan/{id}/presensi/single', [PresensiController::class, 'single'])->name('api.v1.presensi.single');
            Route::post('kegiatan/{id}/presensi/bulk', [PresensiController::class, 'bulk'])->name('api.v1.presensi.bulk');
        });
    });
});
