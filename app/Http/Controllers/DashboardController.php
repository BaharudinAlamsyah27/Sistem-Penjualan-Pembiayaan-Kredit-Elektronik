<?php

namespace App\Http\Controllers;

use App\Models\Angsuran;
use App\Models\Cabang;
use App\Models\SalesOrder;
use App\Services\LabaService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $isAdminPusat = $user->hasRole('admin_pusat');
        $cabangs = $isAdminPusat ? Cabang::query()->orderBy('id')->get() : collect();

        $cabangId = null;
        if ($isAdminPusat && $request->filled('cabang')) {
            $cabangId = $cabangs->firstWhere('id', (int) $request->query('cabang'))?->id;
        }

        $today = Carbon::today()->toDateString();
        $laba = LabaService::bulanBerjalan($cabangId);

        $piutangBerjalan = (float) Angsuran::query()
            ->when($cabangId, fn ($q) => $q->where('cabang_id', $cabangId))
            ->where('jenis', 'angsuran')
            ->where('status', '!=', 'lunas')
            ->sum('nominal');

        $angsuranMasukHariIni = (float) Angsuran::query()
            ->when($cabangId, fn ($q) => $q->where('cabang_id', $cabangId))
            ->whereDate('tgl_bayar', $today)
            ->sum('nominal_dibayar');

        $soMenungguSurvey = (int) SalesOrder::query()
            ->when($cabangId, fn ($q) => $q->where('cabang_id', $cabangId))
            ->where('status', 'survey')
            ->whereHas('survey', fn ($q) => $q->where('hasil', 'menunggu'))
            ->count();

        $pelangganJatuhTempo = (int) Angsuran::query()
            ->when($cabangId, fn ($q) => $q->where('cabang_id', $cabangId))
            ->where('jenis', 'angsuran')
            ->where('status', '!=', 'lunas')
            ->whereDate('jatuh_tempo', '<=', $today)
            ->with('faktur.salesOrder')
            ->get()
            ->pluck('faktur.salesOrder.pelanggan_id')
            ->filter()
            ->unique()
            ->count();

        $tagihanHariIni = Angsuran::query()
            ->when($cabangId, fn ($q) => $q->where('cabang_id', $cabangId))
            ->with(['faktur.salesOrder.pelanggan', 'kolektor', 'faktur.salesOrder.sales'])
            ->where('jenis', 'angsuran')
            ->whereDate('jatuh_tempo', $today)
            ->orderByRaw("CASE WHEN status = 'lunas' THEN 1 ELSE 0 END")
            ->orderBy('id')
            ->get();

        $tabelTagihan = $tagihanHariIni->map(function (Angsuran $angsuran) {
            $so = $angsuran->faktur?->salesOrder;
            $kolektor = $angsuran->kolektor ?? $so?->sales;
            $lunasHariIni = $angsuran->status === 'lunas';

            return [
                'no_faktur' => $angsuran->faktur?->no_faktur ?? '-',
                'nama_pelanggan' => $so?->pelanggan?->nama ?? 'Pelanggan',
                'keterangan_angsuran' => 'Angsuran ke '.$angsuran->angsuran_ke.' dari '.($so?->tenor ?? '-'),
                'nominal' => $this->formatRupiah((float) $angsuran->nominal),
                'kolektor_nama' => $kolektor?->nama ?? 'Petugas',
                'kolektor_area' => $kolektor?->area ? '('.$kolektor->area.')' : '',
                'status' => $lunasHariIni ? 'Lunas Hari Ini' : 'Menunggu Pembayaran',
                'status_class' => $lunasHariIni ? 'lunas' : 'menunggu',
            ];
        })->all();

        $labaKeteranganClass = ($laba['persen'] !== null && $laba['persen'] < 0)
            ? 'text-rose-500'
            : 'text-emerald-600';

        return view('dashboard', [
            'isAdminPusat' => $isAdminPusat,
            'cabangs' => $cabangs,
            'cabangTerpilih' => $cabangId,
            'labaBersihNominal' => $this->formatRupiah($laba['nominal']),
            'labaBersihKeterangan' => $laba['keterangan'],
            'labaKeteranganClass' => $labaKeteranganClass,
            'totalPiutang' => $this->formatRupiah($piutangBerjalan),
            'pelangganJatuhTempoLabel' => $pelangganJatuhTempo.' Pelanggan Jatuh Tempo',
            'angsuranHariIni' => $this->formatRupiah($angsuranMasukHariIni),
            'angsuranHariIniKeterangan' => $angsuranMasukHariIni > 0 ? 'Sesuai target harian' : 'Belum ada penerimaan hari ini',
            'soMenungguSurvey' => $soMenungguSurvey.' Order',
            'tabelTagihan' => $tabelTagihan,
        ]);
    }

    private function formatRupiah(float $nominal): string
    {
        return 'Rp '.number_format($nominal, 0, ',', '.');
    }
}
