<?php

namespace App\Services;

use App\Models\MutasiStok;
use App\Models\StokBarang;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StokService
{
    public function aturSaldo(int $cabangId, int $barangId, int $jumlah, ?string $referensiType = null, ?int $referensiId = null): void
    {
        DB::transaction(function () use ($cabangId, $barangId, $jumlah, $referensiType, $referensiId) {
            $stok = StokBarang::query()
                ->where('cabang_id', $cabangId)
                ->where('barang_id', $barangId)
                ->lockForUpdate()
                ->first();

            if (! $stok) {
                $stok = StokBarang::create(['cabang_id' => $cabangId, 'barang_id' => $barangId, 'qty' => 0]);
            }

            $saldoSebelum = (int) $stok->qty;
            if ($saldoSebelum === $jumlah) {
                return;
            }

            $stok->update(['qty' => $jumlah]);
            MutasiStok::create([
                'cabang_id' => $cabangId,
                'barang_id' => $barangId,
                'tipe' => 'penyesuaian',
                'qty' => abs($jumlah - $saldoSebelum),
                'referensi_type' => $referensiType,
                'referensi_id' => $referensiId,
                'tanggal' => now()->toDateString(),
                'keterangan' => 'Saldo stok disesuaikan dari '.$saldoSebelum.' menjadi '.$jumlah.' unit.',
                'user_id' => auth()->id(),
            ]);
        });
    }

    public function tambah(int $cabangId, int $barangId, int $qty, ?string $referensiType = null, ?int $referensiId = null, ?string $keterangan = null): void
    {
        DB::transaction(function () use ($cabangId, $barangId, $qty, $referensiType, $referensiId, $keterangan) {
            $stok = StokBarang::query()->where('cabang_id', $cabangId)->where('barang_id', $barangId)->lockForUpdate()->first();
            if (! $stok) {
                $stok = StokBarang::create(['cabang_id' => $cabangId, 'barang_id' => $barangId, 'qty' => 0]);
            }
            $stok->increment('qty', $qty);
            MutasiStok::create([
                'cabang_id' => $cabangId, 'barang_id' => $barangId, 'tipe' => 'masuk', 'qty' => $qty,
                'referensi_type' => $referensiType, 'referensi_id' => $referensiId,
                'tanggal' => now()->toDateString(), 'keterangan' => $keterangan, 'user_id' => auth()->id(),
            ]);
        });
    }

    public function kurangi(int $cabangId, int $barangId, int $qty, ?string $referensiType = null, ?int $referensiId = null, ?string $keterangan = null): void
    {
        DB::transaction(function () use ($cabangId, $barangId, $qty, $referensiType, $referensiId, $keterangan) {
            $stok = StokBarang::query()
                ->where('cabang_id', $cabangId)
                ->where('barang_id', $barangId)
                ->lockForUpdate()
                ->first();

            if (! $stok || $stok->qty < $qty) {
                throw ValidationException::withMessages([
                    'stok' => 'Stok barang tidak mencukupi untuk menerbitkan faktur.',
                ]);
            }

            $stok->decrement('qty', $qty);
            MutasiStok::create([
                'cabang_id' => $cabangId,
                'barang_id' => $barangId,
                'tipe' => 'keluar',
                'qty' => $qty,
                'referensi_type' => $referensiType,
                'referensi_id' => $referensiId,
                'tanggal' => now()->toDateString(),
                'keterangan' => $keterangan,
                'user_id' => auth()->id(),
            ]);
        });
    }
}
