<?php

namespace App\Http\Controllers;

use App\Models\Angsuran;
use App\Models\Faktur;
use App\Models\Setting;
use App\Models\SuratJalan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CetakController extends Controller
{
    public function notaDp(Angsuran $angsuran): View
    {
        abort_unless($angsuran->jenis === 'dp', 404);
        $this->authorize('view', $angsuran->faktur);

        return view('cetak.nota-dp', ['angsuran' => $angsuran->load('faktur.salesOrder.pelanggan', 'faktur.salesOrder.barang')]);
    }

    public function faktur(Faktur $faktur): View
    {
        $this->authorize('view', $faktur);

        return view('cetak.faktur', ['faktur' => $faktur->load('salesOrder.pelanggan', 'salesOrder.barang', 'angsurans')]);
    }

    public function kwitansi(Angsuran $angsuran): View
    {
        abort_unless($angsuran->jenis === 'angsuran' && $angsuran->status === 'lunas', 404);
        $this->authorize('view', $angsuran->faktur);

        return view('cetak.kwitansi', [
            'angsuran' => $angsuran->load('faktur.salesOrder.pelanggan', 'faktur.salesOrder.barang'),
            'template' => $this->templateDataUri(),
            'grid' => false,
        ]);
    }

    public function suratJalan(SuratJalan $suratJalan): View
    {
        $this->authorize('view', $suratJalan);

        return view('cetak.surat-jalan', ['suratJalan' => $suratJalan->load('salesOrder.pelanggan', 'salesOrder.barang', 'driver')]);
    }

    public function pratinjau(Request $request): View
    {
        abort_unless($request->user()?->hasRole('admin_pusat'), 403);
        abort_unless($template = $this->templateDataUri(), 404, 'Unggah template kwitansi terlebih dahulu.');

        return view('cetak.kalibrasi', ['template' => $template, 'grid' => $request->boolean('grid')]);
    }

    private function templateDataUri(): ?string
    {
        $path = Setting::get('template_kwitansi_path');
        if (! is_string($path) || ! Storage::disk('local')->exists($path)) {
            return null;
        }

        $mime = Storage::disk('local')->mimeType($path);
        if (! in_array($mime, ['image/jpeg', 'image/png'], true)) {
            return null;
        }

        return 'data:'.$mime.';base64,'.base64_encode(Storage::disk('local')->get($path));
    }
}
