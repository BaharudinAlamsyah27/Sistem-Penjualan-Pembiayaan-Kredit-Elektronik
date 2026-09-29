<?php

namespace App\Http\Controllers;

use App\Http\Requests\PelangganRequest;
use App\Models\Cabang;
use App\Models\Pelanggan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PelangganController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Pelanggan::class, 'pelanggan');
    }

    public function index(Request $request): View
    {
        $sort = $request->query('sort', 'nama');
        $direction = $request->query('direction', 'asc');
        if (! in_array($sort, ['nama', 'kode', 'kabupaten', 'kecamatan'], true)) {
            $sort = 'nama';
        }
        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }
        $query = Pelanggan::query()->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w->where('nama', 'like', '%'.$request->q.'%')->orWhere('kode', 'like', '%'.$request->q.'%')->orWhere('no_hp', 'like', '%'.$request->q.'%')))->when($request->filled('kabupaten'), fn ($q) => $q->where('kabupaten', $request->kabupaten))->when($request->filled('kecamatan'), fn ($q) => $q->where('kecamatan', $request->kecamatan))->when($request->filled('cabang') && auth()->user()->hasRole('admin_pusat'), fn ($q) => $q->where('cabang_id', $request->cabang))->orderBy($sort, $direction);

        return view('master.pelanggan.index', ['pelanggans' => $query->paginate(15)->withQueryString(), 'kabupatens' => Pelanggan::query()->select('kabupaten')->distinct()->orderBy('kabupaten')->pluck('kabupaten'), 'kecamatans' => Pelanggan::query()->select('kecamatan')->distinct()->orderBy('kecamatan')->pluck('kecamatan'), 'cabangs' => auth()->user()->hasRole('admin_pusat') ? Cabang::orderBy('nama')->get() : collect(), 'sort' => $sort, 'direction' => $direction]);
    }

    public function create(): View
    {
        return view('master.pelanggan.form', ['pelanggan' => new Pelanggan, 'cabangs' => $this->cabangs()]);
    }

    public function store(PelangganRequest $request): RedirectResponse
    {
        Pelanggan::create($request->validated());

        return redirect()->route('pelanggan.index')->with('success', 'Data pelanggan berhasil ditambahkan.');
    }

    public function edit(Pelanggan $pelanggan): View
    {
        return view('master.pelanggan.form', compact('pelanggan') + ['cabangs' => $this->cabangs()]);
    }

    public function update(PelangganRequest $request, Pelanggan $pelanggan): RedirectResponse
    {
        $pelanggan->update($request->validated());

        return redirect()->route('pelanggan.index')->with('success', 'Data pelanggan berhasil diperbarui.');
    }

    public function destroy(Pelanggan $pelanggan): RedirectResponse
    {
        $pelanggan->delete();

        return redirect()->route('pelanggan.index')->with('success', 'Data pelanggan berhasil dihapus.');
    }

    private function cabangs()
    {
        return auth()->user()->hasRole('admin_pusat') ? Cabang::orderBy('nama')->get() : collect();
    }
}
