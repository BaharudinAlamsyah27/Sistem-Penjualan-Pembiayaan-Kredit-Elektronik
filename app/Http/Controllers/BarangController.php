<?php

namespace App\Http\Controllers;

use App\Http\Requests\BarangRequest;
use App\Models\Barang;
use App\Models\Cabang;
use App\Models\StokBarang;
use App\Services\StokService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BarangController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Barang::class, 'barang');
    }

    public function index(Request $request): View
    {
        $barangs = Barang::with([
            'stokBarangs' => fn ($q) => $q->when($request->filled('cabang') && auth()->user()->hasRole('admin_pusat'), fn ($s) => $s->where('cabang_id', $request->cabang)),
            'stokBarangs.cabang',
        ])->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w->where('nama', 'like', '%'.$request->q.'%')->orWhere('kode', 'like', '%'.$request->q.'%')->orWhere('merk', 'like', '%'.$request->q.'%')))->orderBy('nama')->paginate(15)->withQueryString();

        return view('master.barang.index', ['barangs' => $barangs, 'cabangs' => auth()->user()->hasRole('admin_pusat') ? Cabang::orderBy('nama')->get() : collect(), 'cabangTerpilih' => $request->query('cabang')]);
    }

    public function create(): View
    {
        return view('master.barang.form', [
            'barang' => new Barang,
            'cabangsStok' => $this->cabangsStok(),
            'saldoStok' => collect(),
        ]);
    }

    public function store(BarangRequest $request, StokService $stokService): RedirectResponse
    {
        $data = $request->validated();
        $saldoStok = $data['stok'];
        unset($data['stok']);

        DB::transaction(function () use ($data, $saldoStok, $stokService) {
            $barang = Barang::create($data);
            foreach ($saldoStok as $cabangId => $jumlah) {
                $stokService->aturSaldo((int) $cabangId, $barang->id, (int) $jumlah, Barang::class, $barang->id);
            }
        });

        return redirect()->route('barang.index')->with('success', 'Data barang dan saldo stok berhasil disimpan.');
    }

    public function edit(Barang $barang): View
    {
        return view('master.barang.form', [
            'barang' => $barang,
            'cabangsStok' => $this->cabangsStok(),
            'saldoStok' => StokBarang::where('barang_id', $barang->id)->pluck('qty', 'cabang_id'),
        ]);
    }

    public function update(BarangRequest $request, Barang $barang, StokService $stokService): RedirectResponse
    {
        $data = $request->validated();
        $saldoStok = $data['stok'];
        unset($data['stok']);

        DB::transaction(function () use ($barang, $data, $saldoStok, $stokService) {
            $barang->update($data);
            foreach ($saldoStok as $cabangId => $jumlah) {
                $stokService->aturSaldo((int) $cabangId, $barang->id, (int) $jumlah, Barang::class, $barang->id);
            }
        });

        return redirect()->route('barang.index')->with('success', 'Data barang dan saldo stok berhasil diperbarui.');
    }

    public function destroy(Barang $barang): RedirectResponse
    {
        $barang->delete();

        return redirect()->route('barang.index')->with('success', 'Data barang berhasil dihapus.');
    }

    private function cabangsStok()
    {
        if (auth()->user()->hasRole('admin_pusat')) {
            return Cabang::orderBy('nama')->get();
        }

        return Cabang::whereKey(auth()->user()->cabang_id)->get();
    }
}
