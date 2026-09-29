<?php

namespace App\Http\Controllers;

use App\Models\Angsuran;
use App\Models\Cabang;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LaporanTagihanController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        abort_unless($user->hasRole(['admin_pusat', 'admin_cabang']), 403);

        $filter = $request->validate([
            'bulan' => ['nullable', 'date_format:Y-m'],
            'kolektor' => ['nullable', 'integer'],
            'kabupaten' => ['nullable', 'string', 'max:100'],
            'kecamatan' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:belum,menunggu_pembayaran,lunas'],
            'cabang' => ['nullable', 'integer'],
        ]);

        $query = Angsuran::query()->with(['faktur.salesOrder.pelanggan', 'kolektor'])
            ->where('jenis', 'angsuran')
            ->when($filter['bulan'] ?? null, fn ($q, $bulan) => $q->whereYear('jatuh_tempo', substr($bulan, 0, 4))->whereMonth('jatuh_tempo', substr($bulan, 5, 2)))
            ->when($filter['kolektor'] ?? null, fn ($q, $id) => $q->where('kolektor_id', $id))
            ->when($filter['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($user->hasRole('admin_pusat') && ($filter['cabang'] ?? null), fn ($q, $id) => $q->where('cabang_id', $id))
            ->whereHas('faktur.salesOrder.pelanggan', fn ($q) => $q
                ->when($filter['kabupaten'] ?? null, fn ($pelanggan, $area) => $pelanggan->where('kabupaten', $area))
                ->when($filter['kecamatan'] ?? null, fn ($pelanggan, $area) => $pelanggan->where('kecamatan', $area))
            )
            ->orderBy('jatuh_tempo')->orderBy('faktur_id')->orderBy('angsuran_ke');

        return view('laporan.tagihan', [
            'angsurans' => $query->paginate(20)->withQueryString(),
            'filters' => $filter,
            'kolektors' => Karyawan::query()->where('jabatan', 'kolektor')->orderBy('nama')->get(),
            'cabangs' => $user->hasRole('admin_pusat') ? Cabang::orderBy('nama')->get() : collect(),
            'totalTagihan' => (clone $query)->reorder()->sum('nominal'),
            'totalDibayar' => (clone $query)->reorder()->sum('nominal_dibayar'),
        ]);
    }
}
