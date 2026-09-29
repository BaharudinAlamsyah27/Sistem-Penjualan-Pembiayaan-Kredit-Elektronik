<?php

namespace App\Services;

use App\Models\SalesOrder;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class LabaService
{
    /**
     * MVP: laba bersih = jumlah total_margin SO berstatus terkirim pada periode.
     * Definisi ini sementara dan mudah diganti.
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

        $nominal = (float) static::queryTerkirim($cabangId)
            ->whereBetween('tanggal', [$awalBulanIni->toDateString(), $akhirBulanIni->toDateString()])
            ->sum('total_margin');

        $nominalLalu = (float) static::queryTerkirim($cabangId)
            ->whereBetween('tanggal', [$awalBulanLalu->toDateString(), $akhirBulanLalu->toDateString()])
            ->sum('total_margin');

        $persen = null;
        if ($nominalLalu > 0) {
            $persen = round((($nominal - $nominalLalu) / $nominalLalu) * 100, 1);
            $tanda = $persen > 0 ? '+' : '';
            $keterangan = $tanda.$persen.'% dari bulan lalu';
        } elseif ($nominal > 0) {
            $keterangan = 'Belum ada data bulan lalu';
        } else {
            $keterangan = 'Belum ada penjualan terkirim bulan ini';
        }

        return [
            'nominal' => $nominal,
            'nominal_lalu' => $nominalLalu,
            'persen' => $persen,
            'keterangan' => $keterangan,
        ];
    }

    private static function queryTerkirim(?int $cabangId): Builder
    {
        return SalesOrder::query()
            ->where('status', 'terkirim')
            ->when($cabangId, fn (Builder $q) => $q->where('cabang_id', $cabangId));
    }
}
