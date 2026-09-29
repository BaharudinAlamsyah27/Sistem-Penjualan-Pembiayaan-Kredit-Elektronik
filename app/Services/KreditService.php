<?php

namespace App\Services;

use App\Models\Angsuran;
use App\Models\Faktur;
use App\Models\SalesOrder;
use App\Models\Setting;
use Carbon\Carbon;

class KreditService
{
    /**
     * Hitung kalkulasi margin kredit NexaERP
     */
    public static function hitung(float $harga, float $dp, int $tenor, float $bunga): array
    {
        $pokok = $harga - $dp;
        $margin = $pokok * ($bunga / 100) * $tenor;
        $total = $pokok + $margin;

        return [
            'pokok_piutang' => $pokok,
            'total_margin' => $margin,
            'total_piutang' => $total,
            'total_kredit' => $total,
            'angsuran_per_bulan' => round($total / $tenor),
        ];
    }

    /**
     * Generate jadwal angsuran otomatis dengan tanggal presisi
     */
    public static function generateJadwalAngsuran(Faktur $faktur, SalesOrder $so): void
    {
        $dpHitungKe1 = Setting::get('dp_hitung_angsuran_pertama', false);
        $sisaTenor = $so->tenor - ($dpHitungKe1 ? 1 : 0);
        $tglDp = Carbon::parse($so->tgl_dp ?? now());

        for ($i = 1; $i <= $sisaTenor; $i++) {
            $angsuranKe = $dpHitungKe1 ? ($i + 1) : $i;

            Angsuran::create([
                'cabang_id' => $so->cabang_id,
                'faktur_id' => $faktur->id,
                'angsuran_ke' => $angsuranKe,
                'nominal' => $so->angsuran_per_bulan,
                'jatuh_tempo' => $tglDp->copy()->addMonthsNoOverflow($i),
                'kolektor_id' => null,
                'status' => 'belum',
                'tgl_bayar' => null,
            ]);
        }
    }
}
