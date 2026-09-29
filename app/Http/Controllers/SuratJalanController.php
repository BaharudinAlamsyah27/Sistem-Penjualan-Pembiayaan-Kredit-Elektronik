<?php

namespace App\Http\Controllers;

use App\Models\SuratJalan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SuratJalanController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', SuratJalan::class);

        return view('surat-jalan.index', [
            'suratJalans' => SuratJalan::with(['salesOrder.pelanggan', 'salesOrder.barang', 'driver'])
                ->when($request->filled('status'), fn ($query) => $query->where('status_verifikasi', $request->query('status')))
                ->latest('tgl_kirim')->paginate(15)->withQueryString(),
        ]);
    }

    public function verify(Request $request, SuratJalan $suratJalan): RedirectResponse
    {
        $this->authorize('verify', $suratJalan);

        DB::transaction(function () use ($suratJalan) {
            $locked = SuratJalan::query()->whereKey($suratJalan->id)->lockForUpdate()->firstOrFail();
            if ($locked->status_verifikasi === 'menunggu') {
                $locked->update(['status_verifikasi' => 'terverifikasi']);
            }
        });

        return back()->with('success', 'Surat jalan berhasil diverifikasi.');
    }
}
