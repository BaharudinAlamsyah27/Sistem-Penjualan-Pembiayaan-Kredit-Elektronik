<?php

namespace App\Http\Controllers;

use App\Models\SuratJalan;
use Illuminate\Http\Request;
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
}
