<?php

namespace App\Http\Controllers;

use App\Http\Requests\PembelianRequest;
use App\Models\Barang;
use App\Models\Cabang;
use App\Models\Pembelian;
use App\Services\StokService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PembelianController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Pembelian::class);

        return view('master.pembelian.index', ['pembelians' => Pembelian::with(['details.barang', 'cabang', 'user'])->latest('tanggal')->paginate(15), 'isAdminPusat' => auth()->user()->hasRole('admin_pusat')]);
    }

    public function create(): View
    {
        $this->authorize('create', Pembelian::class);

        return view('master.pembelian.form', ['barangs' => Barang::where('aktif', true)->orderBy('nama')->get(), 'cabangs' => auth()->user()->hasRole('admin_pusat') ? Cabang::where('aktif', true)->orderBy('nama')->get() : collect()]);
    }

    public function store(PembelianRequest $request, StokService $stokService): RedirectResponse
    {
        $this->authorize('create', Pembelian::class);
        $data = $request->validated();
        $cabangId = (int) $data['cabang_id'];
        $pembelian = DB::transaction(function () use ($data, $cabangId, $stokService) {
            $totalSen = 0;
            foreach ($data['items'] as $item) {
                [$rupiah, $sen] = array_pad(explode('.', (string) $item['harga'], 2), 2, '');
                $hargaPerUnitSen = ((int) $rupiah * 100) + (int) str_pad(substr($sen, 0, 2), 2, '0');
                $totalSen += $hargaPerUnitSen * (int) $item['qty'];
            }
            $total = intdiv($totalSen, 100).'.'.str_pad((string) ($totalSen % 100), 2, '0', STR_PAD_LEFT);
            $pembelian = Pembelian::create(['cabang_id' => $cabangId, 'no_pembelian' => 'PB-'.now()->format('YmdHis').'-'.str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT), 'tanggal' => $data['tanggal'], 'supplier' => $data['supplier'], 'metode' => $data['metode'], 'total' => $total, 'user_id' => auth()->id()]);
            foreach ($data['items'] as $item) {
                $pembelian->details()->create(['barang_id' => $item['barang_id'], 'qty' => $item['qty'], 'harga' => $item['harga']]);
                $stokService->tambah($cabangId, (int) $item['barang_id'], (int) $item['qty'], Pembelian::class, $pembelian->id, 'Stok masuk dari '.$pembelian->no_pembelian);
            }

            return $pembelian;
        });

        return redirect()->route('pembelian.index')->with('success', 'Pembelian '.$pembelian->no_pembelian.' berhasil dicatat dan stok telah diperbarui.');
    }
}
