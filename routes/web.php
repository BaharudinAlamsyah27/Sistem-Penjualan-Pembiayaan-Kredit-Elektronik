<?php

use App\Http\Controllers\CabangController;
use App\Http\Controllers\CetakController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\PembelianController;
use App\Http\Controllers\PenerimaanAngsuranController;
use App\Http\Controllers\LaporanTagihanController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\PenerbitanFakturController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SalesOrderController;
use App\Http\Controllers\SurveyController;
use App\Http\Controllers\SuratJalanController;
use App\Http\Controllers\TemplateKwitansiController;
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
    Route::get('/sales-order/{sales_order}/faktur/create', [PenerbitanFakturController::class, 'create'])->name('sales-order.faktur.create');
    Route::post('/sales-order/{sales_order}/faktur', [PenerbitanFakturController::class, 'store'])->name('sales-order.faktur.store');
    Route::post('/sales-order/{sales_order}/terkirim', [PenerbitanFakturController::class, 'tandaiTerkirim'])->name('sales-order.terkirim');
    Route::get('/survey', [SurveyController::class, 'index'])->name('survey.index');
    Route::get('/survey/{survey}/edit', [SurveyController::class, 'edit'])->name('survey.edit');
    Route::put('/survey/{survey}', [SurveyController::class, 'update'])->name('survey.update');
    Route::get('/angsuran', [PenerimaanAngsuranController::class, 'index'])->name('angsuran.index');
    Route::post('/angsuran/{angsuran}', [PenerimaanAngsuranController::class, 'store'])->name('angsuran.store');
    Route::put('/angsuran/{angsuran}', [PenerimaanAngsuranController::class, 'update'])->name('angsuran.update');
    Route::get('/surat-jalan', [SuratJalanController::class, 'index'])->name('surat-jalan.index');
    Route::post('/surat-jalan/{suratJalan}/verifikasi', [SuratJalanController::class, 'verify'])->name('surat-jalan.verify');
    Route::get('/laporan/penjualan', [LaporanController::class, 'penjualan'])->name('laporan.penjualan');
    Route::get('/laporan/kolektor', [LaporanController::class, 'kolektor'])->name('laporan.kolektor');
    Route::get('/laporan/laba-rugi', [LaporanController::class, 'labaRugi'])->name('laporan.laba-rugi');
    Route::get('/laporan/export', [LaporanController::class, 'export'])->name('laporan.export');
    Route::get('/laporan/tagihan', [LaporanTagihanController::class, 'index'])->name('laporan.tagihan');
    Route::get('/pengaturan/template-kwitansi', [TemplateKwitansiController::class, 'index'])->name('pengaturan.template-kwitansi.index');
    Route::post('/pengaturan/template-kwitansi', [TemplateKwitansiController::class, 'upload'])->name('pengaturan.template-kwitansi.upload');
    Route::get('/pengaturan/template-kwitansi/pratinjau', [TemplateKwitansiController::class, 'preview'])->name('pengaturan.template-kwitansi.preview');
    Route::get('/pengaturan/template-kwitansi/kalibrasi', [CetakController::class, 'pratinjau'])->name('pengaturan.template-kwitansi.kalibrasi');
    Route::delete('/pengaturan/template-kwitansi', [TemplateKwitansiController::class, 'destroy'])->name('pengaturan.template-kwitansi.destroy');
    Route::get('/cetak/angsuran/{angsuran}/nota-dp', [CetakController::class, 'notaDp'])->name('cetak.nota-dp');
    Route::get('/cetak/faktur/{faktur}', [CetakController::class, 'faktur'])->name('cetak.faktur');
    Route::get('/cetak/angsuran/{angsuran}/kwitansi', [CetakController::class, 'kwitansi'])->name('cetak.kwitansi');
    Route::get('/cetak/surat-jalan/{suratJalan}', [CetakController::class, 'suratJalan'])->name('cetak.surat-jalan');
});

require __DIR__.'/auth.php';
