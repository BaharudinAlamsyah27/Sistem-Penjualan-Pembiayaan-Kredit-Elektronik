<?php

namespace App\Services;

use App\Models\Angsuran;
use Carbon\Carbon;

class LabaService
{
    /**
     * MVP: margin diakui sebanding dengan pembayaran angsuran yang lunas.
     *
     * @return array{nominal: float, nominal_lalu: float, persen: float|null, keterangan: string}
     */
    public static function bulanBerjalan(?int $cabangId = null, ?Carbon $acuan = null): array
    {
        $acuan = $acuan ?? Carbon::today();

        $awalBulanIni = $acuan->copy()->startOfMonth();
        $akhirBulanIni = $acuan->copy()->endOfMonth();
        $awalBulanLalu = $acuan->copy()->subMonthNoOverflow()->startOfMonth();
        $akhirBulanLalu = $acuan->copy()->subMonthNoOverflow()->endOfMonth();

        $nominal = static::marginTerbayar($cabangId, $awalBulanIni, $akhirBulanIni);
        $nominalLalu = static::marginTerbayar($cabangId, $awalBulanLalu, $akhirBulanLalu);

        $persen = null;
        if ($nominalLalu > 0) {
            $persen = round((($nominal - $nominalLalu) / $nominalLalu) * 100, 1);
            $tanda = $persen > 0 ? '+' : '';
            $keterangan = $tanda.$persen.'% dari bulan lalu';
        } elseif ($nominal > 0) {
            $keterangan = 'Belum ada data bulan lalu';
        } else {
            $keterangan = 'Belum ada angsuran lunas bulan ini';
        }

        return [
            'nominal' => $nominal,
            'nominal_lalu' => $nominalLalu,
            'persen' => $persen,
            'keterangan' => $keterangan,
        ];
    }

    private static function marginTerbayar(?int $cabangId, Carbon $awal, Carbon $akhir): float
    {
        return Angsuran::query()
            ->with('faktur.salesOrder')
            ->where('jenis', 'angsuran')
            ->where('status', 'lunas')
            ->whereBetween('tgl_bayar', [$awal->toDateString(), $akhir->toDateString()])
            ->when($cabangId, fn ($q) => $q->where('cabang_id', $cabangId))
            ->get()
            ->sum(function (Angsuran $angsuran): float {
                $so = $angsuran->faktur?->salesOrder;
                $total = (float) ($so?->total_kredit ?? 0);

                return $total > 0
                    ? (float) $angsuran->nominal_dibayar * ((float) $so->total_margin / $total)
                    : 0.0;
            });
    }
}
