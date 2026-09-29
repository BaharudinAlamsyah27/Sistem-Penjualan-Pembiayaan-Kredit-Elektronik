<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Sales & Kredit
    Route::get('/pelanggan', fn() => view('placeholder', ['title' => 'Data Pelanggan']))->name('pelanggan.index');
    Route::get('/sales-order', fn() => view('placeholder', ['title' => 'Sales Order (SO)']))->name('sales-order.index');
    Route::get('/survey', fn() => view('placeholder', ['title' => 'Survey Kelayakan']))->name('survey.index');
    Route::get('/angsuran', fn() => view('placeholder', ['title' => 'Penerimaan Angsuran']))->name('angsuran.index');

    // Inventori & Logistik
    Route::get('/barang', fn() => view('placeholder', ['title' => 'Data Barang']))->name('barang.index');
    Route::get('/pembelian', fn() => view('placeholder', ['title' => 'Pembelian & Retur']))->name('pembelian.index');
    Route::get('/surat-jalan', fn() => view('placeholder', ['title' => 'Surat Jalan (TTB)']))->name('surat-jalan.index');

    // Keuangan (Laporan)
    Route::get('/laporan/laba-rugi', fn() => view('placeholder', ['title' => 'Laporan Laba / Rugi']))->name('laporan.laba-rugi');
    Route::get('/laporan/tagihan', fn() => view('placeholder', ['title' => 'Laporan Tagihan']))->name('laporan.tagihan');

    // Master & Setup
    Route::get('/karyawan', fn() => view('placeholder', ['title' => 'Karyawan & Akses']))->name('karyawan.index');

    // Cabang (Hanya admin_pusat)
    Route::get('/cabang', function () {
        if (!auth()->user()->hasRole('admin_pusat')) {
            abort(403, 'Akses khusus Admin Pusat.');
        }
        return view('placeholder', ['title' => 'Data Cabang']);
    })->name('cabang.index');
});

require __DIR__.'/auth.php';
