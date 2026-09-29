<?php

namespace App\Http\Controllers;

use App\Http\Requests\BarangRequest;
use App\Models\Barang;
use App\Models\Cabang;
use App\Models\StokBarang;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        return view('master.barang.form', ['barang' => new Barang, 'stokBarangs' => collect()]);
    }

    public function store(BarangRequest $request): RedirectResponse
    {
        Barang::create($request->validated());

        return redirect()->route('barang.index')->with('success', 'Data barang berhasil ditambahkan.');
    }

    public function edit(Barang $barang): View
    {
        return view('master.barang.form', [
            'barang' => $barang,
            'stokBarangs' => StokBarang::with('cabang')->where('barang_id', $barang->id)->orderBy('cabang_id')->get(),
        ]);
    }

    public function update(BarangRequest $request, Barang $barang): RedirectResponse
    {
        $barang->update($request->validated());

        return redirect()->route('barang.index')->with('success', 'Data barang berhasil diperbarui.');
    }

    public function destroy(Barang $barang): RedirectResponse
    {
        $barang->delete();

        return redirect()->route('barang.index')->with('success', 'Data barang berhasil dihapus.');
    }
}
