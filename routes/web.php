<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PendataanController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PersebaranController;
use App\Http\Controllers\Admin\PemudaController;
use App\Http\Controllers\Admin\WilayahController;
use App\Http\Controllers\Admin\CabangController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Admin\WargaMtaController;
use App\Http\Controllers\Admin\MtaSyncController;
use App\Http\Controllers\Admin\HomepageSettingController;
use App\Http\Controllers\Admin\AjaxController;

// ==========================================
// 1. PUBLIC ROUTES
// ==========================================
Route::get('/', [HomeController::class, 'index'])->name('home');

Route::prefix('pendataan')->name('pendataan.')->group(function () {
    Route::get('/', [PendataanController::class, 'index'])->name('index');
    Route::get('/search-nama', [PendataanController::class, 'searchNama'])->middleware('throttle:120,1')->name('search-nama');
    Route::get('/get-pemuda/{id}', [PendataanController::class, 'getPemudaData'])->middleware('throttle:120,1')->name('get-pemuda');
    Route::get('/get-warga/{uuid}', [PendataanController::class, 'getWargaData'])->middleware('throttle:120,1')->name('get-warga');
    Route::post('/simpan', [PendataanController::class, 'simpan'])->name('simpan');
    Route::get('/sukses', [PendataanController::class, 'sukses'])->name('sukses');
});

// Public API for form dropdowns
Route::get('api/cabang/{wilayahId}', [AjaxController::class, 'getCabangByWilayah'])->name('api.cabang');
Route::get('api/villages/{districtId}', [AjaxController::class, 'getVillagesByDistrict'])->name('api.villages');

// ==========================================
// 2. AUTHENTICATION ROUTES
// ==========================================
Route::get('admin/login', [AuthController::class, 'login'])->name('login');
Route::post('admin/login', [AuthController::class, 'authenticate'])->name('login.post');
Route::post('admin/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('admin/logout', fn () => redirect()->route('login'));

// ==========================================
// 3. ADMIN PANEL (PROTECTED)
// ==========================================
Route::prefix('admin')->middleware('auth.admin')->name('admin.')->group(function () {
    // Dashboard & Persebaran
    Route::get('/', [DashboardController::class, 'index']);
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('persebaran', [PersebaranController::class, 'index'])->name('persebaran');
    Route::get('dashboard/persebaran', [PersebaranController::class, 'index']);

    // Manajemen Data Pemuda
    Route::prefix('pemuda')->name('pemuda.')->group(function () {
        Route::get('/', [PemudaController::class, 'index'])->name('index');
        Route::get('detail/{id}', [PemudaController::class, 'detail'])->name('detail');
        Route::get('tambah', [PemudaController::class, 'tambah'])->name('tambah');
        Route::post('simpan', [PemudaController::class, 'simpan'])->name('simpan');
        Route::get('edit/{id}', [PemudaController::class, 'edit'])->name('edit');
        Route::post('update/{id}', [PemudaController::class, 'update'])->name('update');
        Route::post('verifikasi/{id}', [PemudaController::class, 'verifikasi'])->name('verifikasi');
        Route::post('archive/{id}', [PemudaController::class, 'archive'])->name('archive');
        Route::post('delete/{id}', [PemudaController::class, 'delete'])->name('delete');
        Route::get('export', [PemudaController::class, 'export'])->name('export');
        Route::post('export', [PemudaController::class, 'exportDownload'])->name('export.download');
        Route::get('export/count', [PemudaController::class, 'exportCount'])->name('export.count');
        Route::get('cetak/{id}', [PemudaController::class, 'cetak'])->name('cetak');

        // Khusus Superadmin: Import, Backup & Clear Data
        Route::middleware('role:superadmin')->group(function () {
            Route::get('import', [PemudaController::class, 'import'])->name('import');
            Route::post('import', [PemudaController::class, 'prosesImport'])->name('import.process');
            Route::get('template-import', [PemudaController::class, 'templateImport'])->name('template-import');

            Route::get('backup', [PemudaController::class, 'backup'])->name('backup');
            Route::post('backup/generate', [PemudaController::class, 'generateBackup'])->name('backup.generate');
            Route::get('backup/download/{filename}', [PemudaController::class, 'downloadBackup'])->name('backup.download');
            Route::post('backup/delete-file/{filename}', [PemudaController::class, 'deleteBackupFile'])->name('backup.delete');
            Route::post('hapus-semua', [PemudaController::class, 'hapusSemua'])->name('hapus-semua');
        });
    });

    // Master Wilayah (Superadmin)
    Route::prefix('wilayah')->middleware('role:superadmin')->name('wilayah.')->group(function () {
        Route::get('/', [WilayahController::class, 'index'])->name('index');
        Route::post('simpan', [WilayahController::class, 'simpan'])->name('simpan');
        Route::post('update/{id}', [WilayahController::class, 'update'])->name('update');
        Route::post('delete/{id}', [WilayahController::class, 'delete'])->name('delete');
    });

    // Master Cabang (Superadmin)
    Route::prefix('cabang')->middleware('role:superadmin')->name('cabang.')->group(function () {
        Route::get('/', [CabangController::class, 'index'])->name('index');
        Route::get('detail/{id}', [CabangController::class, 'detail'])->name('detail');
        Route::post('simpan', [CabangController::class, 'simpan'])->name('simpan');
        Route::post('update/{id}', [CabangController::class, 'update'])->name('update');
        Route::post('delete/{id}', [CabangController::class, 'delete'])->name('delete');
    });

    // Master Users & Roles (Superadmin)
    Route::prefix('users')->middleware('role:superadmin')->name('users.')->group(function () {
        Route::get('/', [UsersController::class, 'index'])->name('index');
        Route::post('simpan', [UsersController::class, 'simpan'])->name('simpan');
        Route::post('update/{id}', [UsersController::class, 'update'])->name('update');
        Route::post('delete/{id}', [UsersController::class, 'delete'])->name('delete');
    });

    // Data Warga MTA Sragen dari api.mta.or.id (Superadmin)
    Route::prefix('warga-mta')->middleware('role:superadmin')->name('warga-mta.')->group(function () {
        Route::get('/', [WargaMtaController::class, 'index'])->name('index');
        Route::get('detail/{uuid}', [WargaMtaController::class, 'detail'])->name('detail');
        Route::post('import', [WargaMtaController::class, 'import'])->name('import');
    });

    // Integrasi & Sinkronisasi Database Warga MTA (Superadmin)
    Route::prefix('mta-sync')->middleware('role:superadmin')->name('mta-sync.')->group(function () {
        Route::get('/', [MtaSyncController::class, 'index'])->name('index');
        Route::get('test-connection', [MtaSyncController::class, 'testConnection'])->name('test-connection');
        Route::post('sync-cabang', [MtaSyncController::class, 'syncCabang'])->name('sync-cabang');
        Route::get('search-warga', [MtaSyncController::class, 'searchWarga'])->name('search-warga');
        Route::get('warga-detail/{uuid}', [MtaSyncController::class, 'wargaDetail'])->name('warga-detail');
        Route::get('cabang-warga/{cabangUuid}', [MtaSyncController::class, 'cabangWarga'])->name('cabang-warga');
        Route::post('import-warga', [MtaSyncController::class, 'importWarga'])->name('import-warga');
        Route::post('sync-pemuda/{id}', [MtaSyncController::class, 'syncPemuda'])->name('sync-pemuda');
        Route::post('sync-verify-all', [MtaSyncController::class, 'syncVerifyAll'])->name('sync-verify-all');
        Route::post('queue-init', [MtaSyncController::class, 'queueInit'])->name('queue-init');
        Route::post('queue-process-item', [MtaSyncController::class, 'queueProcessItem'])->name('queue-process-item');
        Route::get('queue-status', [MtaSyncController::class, 'queueStatus'])->name('queue-status');
        Route::post('queue-cancel', [MtaSyncController::class, 'queueCancel'])->name('queue-cancel');
    });

    // Kelola Konten Beranda / Homepage (Superadmin)
    Route::prefix('homepage')->middleware('role:superadmin')->name('homepage.')->group(function () {
        Route::get('/', [HomepageSettingController::class, 'index'])->name('index');
        Route::post('update', [HomepageSettingController::class, 'update'])->name('update');
        Route::post('reset', [HomepageSettingController::class, 'reset'])->name('reset');
    });

    // Ajax Helpers
    Route::get('ajax/cabang/{wilayahId}', [AjaxController::class, 'getCabangByWilayah'])->name('ajax.cabang');
    Route::get('ajax/villages/{districtId}', [AjaxController::class, 'getVillagesByDistrict'])->name('ajax.villages');
});
