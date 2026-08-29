<?php

use App\Http\Controllers\RwController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DusunController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ExportController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Auth/Login');
});

Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Laporan (semua role bisa akses, scope di controller)
    Route::post('/laporan/{laporan}', [LaporanController::class, 'update']);
    Route::resource('laporan', LaporanController::class);
    Route::patch('/laporan/{laporan}/status', [LaporanController::class, 'updateStatus'])->name('laporan.updateStatus');

    // Master Data RW (admin, superadmin, kepala_dusun - kepala_dusun view & edit only in controller)
    Route::middleware('role:superadmin,admin,kepala_dusun')->prefix('master')->name('master.')->group(function () {
        Route::post('rw/{rw}', [RwController::class, 'update']);
        Route::resource('rw', RwController::class)->names('rw');
    });

    // Master Data Lanjutan (admin + superadmin)
    Route::middleware('role:superadmin,admin')->prefix('master')->name('master.')->group(function () {
        Route::post('dusun/{dusun}', [DusunController::class, 'update']);
        Route::resource('dusun', DusunController::class)->names('dusun');
        Route::resource('kategori', KategoriController::class)->names('kategori');
        Route::resource('subkategori', \App\Http\Controllers\SubkategoriController::class)->names('subkategori')->except(['show']);
    });

    // User Management (superadmin only)
    Route::middleware('role:superadmin')->group(function () {
        Route::resource('user', UserController::class)->except(['show']);
    });

    // Export (admin, superadmin, kepala_dusun)
    Route::middleware('role:superadmin,admin,kepala_dusun')->prefix('export')->name('export.')->group(function () {
        Route::get('/', [ExportController::class, 'index'])->name('index');
        Route::get('/csv', [ExportController::class, 'csv'])->name('csv');
        Route::get('/excel', [ExportController::class, 'excel'])->name('excel');
    });
});

require __DIR__.'/auth.php';