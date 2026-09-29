<?php

use App\Http\Controllers\CabangController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\PembelianController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SalesOrderController;
use App\Http\Controllers\SurveyController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('pelanggan', PelangganController::class)->except('show');
    Route::resource('karyawan', KaryawanController::class)->except('show');
    Route::resource('barang', BarangController::class)->except('show');
    Route::get('/pembelian', [PembelianController::class, 'index'])->name('pembelian.index');
    Route::get('/pembelian/create', [PembelianController::class, 'create'])->name('pembelian.create');
    Route::post('/pembelian', [PembelianController::class, 'store'])->name('pembelian.store');

    Route::middleware('role:admin_pusat')->group(function () {
        Route::resource('cabang', CabangController::class)->except('show');
    });

    Route::get('/sales-order', [SalesOrderController::class, 'index'])->name('sales-order.index');
    Route::get('/sales-order/create', [SalesOrderController::class, 'create'])->name('sales-order.create');
    Route::post('/sales-order', [SalesOrderController::class, 'store'])->name('sales-order.store');
    Route::get('/sales-order/{sales_order}', [SalesOrderController::class, 'show'])->name('sales-order.show');
    Route::get('/survey', [SurveyController::class, 'index'])->name('survey.index');
    Route::get('/survey/{survey}/edit', [SurveyController::class, 'edit'])->name('survey.edit');
    Route::put('/survey/{survey}', [SurveyController::class, 'update'])->name('survey.update');
    Route::get('/angsuran', fn () => view('placeholder', ['title' => 'Penerimaan Angsuran']))->name('angsuran.index');
    Route::get('/surat-jalan', fn () => view('placeholder', ['title' => 'Surat Jalan (TTB)']))->name('surat-jalan.index');
    Route::get('/laporan/laba-rugi', fn () => view('placeholder', ['title' => 'Laporan Laba / Rugi']))->name('laporan.laba-rugi');
    Route::get('/laporan/tagihan', fn () => view('placeholder', ['title' => 'Laporan Tagihan']))->name('laporan.tagihan');
});

require __DIR__.'/auth.php';
