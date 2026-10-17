<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\KategoriController;
use App\Http\Controllers\Admin\PenjualanController;
use App\Http\Controllers\Admin\ProdukController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Kasir\DashboardController as KasirDashboardController;
use App\Http\Controllers\Kasir\KasirController;
use App\Http\Controllers\Kasir\LaporanController;
use App\Http\Controllers\Kasir\PelangganController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->controller(AuthController::class)->group(function () {
    Route::get('/login', 'login')->name('login');
    Route::post('/login', 'verif')->name('login.verif');
});

Route::get('/', function() {
    if (Auth::check() && Auth::user()->peran === 'admin') {
    return redirect()->route('admin.dashboard');
    } elseif(Auth::check() && Auth::user()->peran === 'kasir') {
        return redirect()->route('kasir.dashboard');
    } else {
        return redirect()->route('login');
    }
})->name('home');

Route::middleware(['auth'])->group(function() {
    Route::prefix('admin')->as('admin.')->middleware('role:admin')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::resource('user', UserController::class);
        Route::resource('kategori-produk', KategoriController::class)->names('kategori');
        Route::resource('produk', ProdukController::class);
        Route::get('penjualan/export/pdf', [PenjualanController::class, 'cetakPdf'])->name('penjualan.pdf');
        Route::resource('penjualan', PenjualanController::class)->only(['index', 'show']);
    });

    Route::prefix('kasir')->as('kasir.')->middleware('role:kasir')->group(function () {
        Route::get('/', [KasirDashboardController::class, 'index'])->name('dashboard');
        Route::get('/pos', [KasirController::class, 'index'])->name('kasir.index');
        Route::resource('/pelanggan', PelangganController::class);
        Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan');
        Route::get('/laporan/export/pdf', [LaporanController::class, 'cetakPdf'])->name('laporan.pdf');
        Route::get('/laporan/{id}', [LaporanController::class, 'show'])->name('laporan.show');
    });

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});