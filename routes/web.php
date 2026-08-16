<?php

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
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware(['auth', 'verified'])->group(function () {
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

    // Master Data (admin + superadmin)
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

    // Export (admin + superadmin)
    Route::middleware('role:superadmin,admin')->prefix('export')->name('export.')->group(function () {
        Route::get('/', [ExportController::class, 'index'])->name('index');
        Route::get('/csv', [ExportController::class, 'csv'])->name('csv');
    });
});

require __DIR__.'/auth.php';