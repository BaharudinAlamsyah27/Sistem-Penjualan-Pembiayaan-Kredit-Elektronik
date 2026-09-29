<?php

namespace App\Services;

use App\Models\NomorDokumen;
use Illuminate\Support\Facades\DB;

class NomorDokumenService
{
    public function berikutnya(int $cabangId, string $jenis, ?string $tanggal = null): string
    {
        $tanggal ??= now()->format('Ym');
        $periode = preg_match('/^\d{6}$/', $tanggal) ? $tanggal : date('Ym', strtotime($tanggal));

        return DB::transaction(function () use ($cabangId, $jenis, $periode) {
            $now = now();
            DB::table('nomor_dokumens')->insertOrIgnore([
                'cabang_id' => $cabangId,
                'jenis' => $jenis,
                'periode' => $periode,
                'terakhir' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $nomor = NomorDokumen::query()
                ->where('cabang_id', $cabangId)
                ->where('jenis', $jenis)
                ->where('periode', $periode)
                ->lockForUpdate()
                ->firstOrFail();

            $nomor->increment('terakhir');

            return sprintf('%s-%s-%03d', $jenis, $periode, $nomor->terakhir);
        });
    }
}
