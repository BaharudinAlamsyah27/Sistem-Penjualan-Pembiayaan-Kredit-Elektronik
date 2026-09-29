<?php

namespace App\Http\Controllers;

use App\Models\Angsuran;
use App\Models\Cabang;
use App\Models\SalesOrder;
use App\Services\LabaService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanController extends Controller
{
    public function penjualan(Request $request): View
    {
        $filter = $this->filter($request);
        $rows = $this->ringkasanPenjualan($filter);

        return view('laporan.penjualan', [
            'rows' => $rows,
            'filter' => $filter,
            'cabangs' => $request->user()->hasRole('admin_pusat') ? Cabang::orderBy('nama')->get() : collect(),
        ]);
    }

    public function kolektor(Request $request): View
    {
        $filter = $this->filter($request);
        $rows = $this->ringkasanKolektor($filter);

        return view('laporan.kolektor', [
            'rows' => $rows,
            'filter' => $filter,
            'cabangs' => $request->user()->hasRole('admin_pusat') ? Cabang::orderBy('nama')->get() : collect(),
        ]);
    }

    public function labaRugi(Request $request): View
    {
        abort_unless($request->user()->hasRole('admin_pusat'), 403);
        $filter = $request->validate([
            'bulan' => ['nullable', 'date_format:Y-m'],
            'cabang' => ['nullable', 'integer'],
        ]);
        $bulan = Carbon::createFromFormat('Y-m', $filter['bulan'] ?? now()->format('Y-m'))->endOfMonth();
        $ringkasan = LabaService::bulanBerjalan(isset($filter['cabang']) ? (int) $filter['cabang'] : null, $bulan);

        return view('laporan.laba-rugi', [
            'ringkasan' => $ringkasan,
            'bulan' => $bulan->format('Y-m'),
            'cabangs' => Cabang::orderBy('nama')->get(),
            'cabangTerpilih' => $filter['cabang'] ?? null,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $filter = $this->filter($request);
        $jenis = $request->validate(['jenis' => ['required', 'in:penjualan,kolektor']])['jenis'];
        $rows = $jenis === 'penjualan' ? $this->ringkasanPenjualan($filter) : $this->ringkasanKolektor($filter);
        $header = $jenis === 'penjualan'
            ? ['Nama Sales', 'Jumlah Sales Order', 'Nilai Penjualan', 'Margin Kredit']
            : ['Kolektor', 'Jumlah Angsuran', 'Total Tagihan', 'Total Dibayar', 'Tunggakan Lewat Tempo'];
        $filename = 'laporan-'.$jenis.'-'.$filter['dari'].'-'.$filter['sampai'].'.csv';

        return response()->streamDownload(function () use ($header, $rows, $jenis) {
            $stream = fopen('php://output', 'w');
            fwrite($stream, "\xEF\xBB\xBF");
            fputcsv($stream, $header, ';');
            foreach ($rows as $row) {
                fputcsv($stream, $jenis === 'penjualan'
                    ? [$row['nama'], $row['jumlah'], $row['penjualan'], $row['margin']]
                    : [$row['nama'], $row['jumlah'], $row['tagihan'], $row['dibayar'], $row['tunggakan']], ';');
            }
            fclose($stream);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function filter(Request $request): array
    {
        $filter = $request->validate([
            'dari' => ['nullable', 'date_format:Y-m-d'],
            'sampai' => ['nullable', 'date_format:Y-m-d'],
            'cabang' => ['nullable', 'integer'],
        ]);
        $filter['dari'] = $filter['dari'] ?? now()->startOfMonth()->toDateString();
        $filter['sampai'] = $filter['sampai'] ?? now()->toDateString();
        if ($filter['dari'] > $filter['sampai']) {
            throw ValidationException::withMessages(['dari' => 'Tanggal awal harus sebelum atau sama dengan tanggal akhir.']);
        }

        return $filter;
    }

    private function ringkasanPenjualan(array $filter): Collection
    {
        return SalesOrder::query()->with('sales')
            ->where('status', 'terkirim')
            ->whereDate('tanggal', '>=', $filter['dari'])
            ->whereDate('tanggal', '<=', $filter['sampai'])
            ->when($filter['cabang'] ?? null, fn ($q, $id) => $q->where('cabang_id', $id))
            ->get()
            ->groupBy('sales_id')
            ->map(function (Collection $orders): array {
                return [
                    'nama' => $orders->first()->sales?->nama ?? 'Tanpa sales',
                    'jumlah' => $orders->count(),
                    'penjualan' => $orders->sum(fn ($so) => (float) $so->harga_jual * (int) $so->qty),
                    'margin' => $orders->sum(fn ($so) => (float) $so->total_margin),
                ];
            })->values()->sortBy('nama')->values();
    }

    private function ringkasanKolektor(array $filter): Collection
    {
        return Angsuran::query()->with(['kolektor', 'faktur.salesOrder.pelanggan'])
            ->where('jenis', 'angsuran')
            ->whereDate('jatuh_tempo', '>=', $filter['dari'])
            ->whereDate('jatuh_tempo', '<=', $filter['sampai'])
            ->when($filter['cabang'] ?? null, fn ($q, $id) => $q->where('cabang_id', $id))
            ->get()
            ->groupBy('kolektor_id')
            ->map(function (Collection $angsuran): array {
                return [
                    'nama' => $angsuran->first()->kolektor?->nama ?? 'Belum ditugaskan',
                    'jumlah' => $angsuran->count(),
                    'tagihan' => $angsuran->sum(fn ($a) => (float) $a->nominal),
                    'dibayar' => $angsuran->sum(fn ($a) => (float) $a->nominal_dibayar),
                    'tunggakan' => $angsuran->filter(fn ($a) => $a->status !== 'lunas' && $a->jatuh_tempo->lt(Carbon::today()))
                        ->sum(fn ($a) => (float) $a->nominal),
                ];
            })->values()->sortBy('nama')->values();
    }
}
