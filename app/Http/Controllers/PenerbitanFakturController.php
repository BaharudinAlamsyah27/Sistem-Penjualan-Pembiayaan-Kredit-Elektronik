<?php

namespace App\Http\Controllers;

use App\Http\Requests\PenerbitanFakturRequest;
use App\Models\Angsuran;
use App\Models\Faktur;
use App\Models\Karyawan;
use App\Models\SalesOrder;
use App\Models\Setting;
use App\Models\SuratJalan;
use App\Models\StokBarang;
use App\Services\KreditService;
use App\Services\NomorDokumenService;
use App\Services\StokService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PenerbitanFakturController extends Controller
{
    public function create(SalesOrder $sales_order): View|RedirectResponse
    {
        $this->authorize('view', $sales_order);
        $this->authorize('create', Faktur::class);

        if ($sales_order->survey?->hasil !== 'layak' || $sales_order->status !== 'survey') {
            return redirect()->route('sales-order.show', $sales_order)->with('error', 'Sales Order hanya dapat diterbitkan setelah hasil survei dinyatakan layak.');
        }
        if ($sales_order->faktur()->exists()) {
            return redirect()->route('sales-order.show', $sales_order)->with('error', 'Faktur untuk Sales Order ini sudah diterbitkan.');
        }

        return view('sales-order.faktur', [
            'salesOrder' => $sales_order->load(['pelanggan', 'barang']),
            'drivers' => Karyawan::where('cabang_id', $sales_order->cabang_id)->where('jabatan', 'driver')->where('aktif', true)->orderBy('nama')->get(),
        ]);
    }

    public function store(
        PenerbitanFakturRequest $request,
        SalesOrder $sales_order,
        NomorDokumenService $nomorDokumenService,
        StokService $stokService,
    ): RedirectResponse {
        $this->authorize('view', $sales_order);
        $this->authorize('create', Faktur::class);
        $data = $request->validated();

        $faktur = DB::transaction(function () use ($data, $sales_order, $nomorDokumenService, $stokService) {
            $so = SalesOrder::query()->whereKey($sales_order->id)->lockForUpdate()->firstOrFail();
            $so->load('survey');

            if ($so->status !== 'survey' || $so->survey?->hasil !== 'layak' || $so->faktur()->exists()) {
                throw ValidationException::withMessages(['sales_order' => 'Sales Order tidak lagi memenuhi syarat penerbitan faktur.']);
            }

            $stok = StokBarang::query()->where('cabang_id', $so->cabang_id)->where('barang_id', $so->barang_id)->lockForUpdate()->first();
            if (! $stok || $stok->qty < $so->qty) {
                throw ValidationException::withMessages(['stok' => 'Stok barang di cabang ini tidak mencukupi.']);
            }

            $faktur = Faktur::create([
                'cabang_id' => $so->cabang_id,
                'sales_order_id' => $so->id,
                'no_faktur' => $nomorDokumenService->berikutnya($so->cabang_id, 'INV'),
                'tanggal' => now()->toDateString(),
                'tgl_dp' => $data['tgl_dp'],
                'status' => 'aktif',
            ]);

            $dpHitungKe1 = filter_var(Setting::get('dp_hitung_angsuran_pertama', false), FILTER_VALIDATE_BOOLEAN);
            Angsuran::create([
                'cabang_id' => $so->cabang_id,
                'faktur_id' => $faktur->id,
                'jenis' => 'dp',
                'angsuran_ke' => $dpHitungKe1 ? 1 : 0,
                'nominal' => $so->dp,
                'jatuh_tempo' => $data['tgl_dp'],
                'status' => 'lunas',
                'tgl_bayar' => $data['tgl_dp'],
                'nominal_dibayar' => $so->dp,
                'dicatat_oleh' => auth()->id(),
            ]);

            KreditService::generateJadwalAngsuran($faktur, $so);
            SuratJalan::create([
                'cabang_id' => $so->cabang_id,
                'sales_order_id' => $so->id,
                'no_ttb' => $nomorDokumenService->berikutnya($so->cabang_id, 'TTB'),
                'driver_id' => $data['driver_id'],
                'tgl_kirim' => $data['tgl_kirim'],
                'nota' => $data['nota'] ?? null,
                'status_verifikasi' => 'menunggu',
            ]);

            $so->update(['status' => 'disetujui']);
            $stokService->kurangi($so->cabang_id, $so->barang_id, $so->qty, Faktur::class, $faktur->id, 'Stok keluar untuk '.$faktur->no_faktur);

            return $faktur;
        });

        return redirect()->route('sales-order.show', $sales_order)->with('success', 'Faktur '.$faktur->no_faktur.' berhasil diterbitkan.');
    }

    public function tandaiTerkirim(SalesOrder $sales_order): RedirectResponse
    {
        $this->authorize('view', $sales_order);

        DB::transaction(function () use ($sales_order) {
            $so = SalesOrder::query()->whereKey($sales_order->id)->lockForUpdate()->firstOrFail();
            if ($so->status !== 'disetujui' || ! $so->faktur()->exists() || ! $so->suratJalan()->exists()) {
                throw ValidationException::withMessages(['sales_order' => 'Sales Order belum memiliki faktur dan surat jalan aktif.']);
            }
            $so->update(['status' => 'terkirim']);
        });

        return redirect()->route('sales-order.show', $sales_order)->with('success', 'Barang telah ditandai terkirim.');
    }
}
