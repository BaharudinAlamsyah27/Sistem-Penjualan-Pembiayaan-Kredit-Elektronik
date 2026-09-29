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
        $dpHitungKe1 = filter_var(Setting::get('dp_hitung_angsuran_pertama', false), FILTER_VALIDATE_BOOLEAN);
        $jumlah = max(0, (int) $so->tenor - ($dpHitungKe1 ? 1 : 0));
        $totalSen = self::keSen((string) $so->total_kredit);
        $angsuranBiasaSen = self::keSen((string) $so->angsuran_per_bulan);
        $sudahSen = 0;
        $tglDp = Carbon::parse($faktur->tgl_dp);

        for ($i = 1; $i <= $jumlah; $i++) {
            $nominalSen = $i === $jumlah ? $totalSen - $sudahSen : $angsuranBiasaSen;
            $sudahSen += $nominalSen;
            $angsuranKe = $dpHitungKe1 ? $i + 1 : $i;

            Angsuran::create([
                'cabang_id' => $so->cabang_id,
                'faktur_id' => $faktur->id,
                'jenis' => 'angsuran',
                'angsuran_ke' => $angsuranKe,
                'nominal' => self::dariSen($nominalSen),
                'jatuh_tempo' => $tglDp->copy()->addMonthsNoOverflow($i),
                'kolektor_id' => null,
                'status' => 'belum',
            ]);
        }
    }

    private static function keSen(string $nominal): int
    {
        [$rupiah, $sen] = array_pad(explode('.', $nominal, 2), 2, '');

        return ((int) $rupiah * 100) + (int) str_pad(substr($sen, 0, 2), 2, '0');
    }

    private static function dariSen(int $nominal): string
    {
        return intdiv($nominal, 100).'.'.str_pad((string) ($nominal % 100), 2, '0', STR_PAD_LEFT);
    }
}
