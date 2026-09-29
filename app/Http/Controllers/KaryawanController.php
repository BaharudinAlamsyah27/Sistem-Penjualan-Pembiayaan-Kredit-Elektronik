<?php

namespace App\Http\Controllers;

use App\Http\Requests\KaryawanRequest;
use App\Models\Cabang;
use App\Models\Karyawan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KaryawanController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Karyawan::class, 'karyawan');
    }

    public function index(Request $request): View
    {
        $query = Karyawan::with('cabang')->when($request->filled('jabatan'), fn ($q) => $q->where('jabatan', $request->jabatan))->when($request->filled('cabang') && auth()->user()->hasRole('admin_pusat'), fn ($q) => $q->where('cabang_id', $request->cabang))->orderBy('nama');

        return view('master.karyawan.index', ['karyawans' => $query->paginate(15)->withQueryString(), 'cabangs' => auth()->user()->hasRole('admin_pusat') ? Cabang::orderBy('nama')->get() : collect()]);
    }

    public function create(): View
    {
        return view('master.karyawan.form', ['karyawan' => new Karyawan, 'cabangs' => $this->cabangs()]);
    }

    public function store(KaryawanRequest $request): RedirectResponse
    {
        Karyawan::create($request->validated());

        return redirect()->route('karyawan.index')->with('success', 'Data karyawan berhasil ditambahkan.');
    }

    public function edit(Karyawan $karyawan): View
    {
        return view('master.karyawan.form', compact('karyawan') + ['cabangs' => $this->cabangs()]);
    }

    public function update(KaryawanRequest $request, Karyawan $karyawan): RedirectResponse
    {
        $karyawan->update($request->validated());

        return redirect()->route('karyawan.index')->with('success', 'Data karyawan berhasil diperbarui.');
    }

    public function destroy(Karyawan $karyawan): RedirectResponse
    {
        $karyawan->delete();

        return redirect()->route('karyawan.index')->with('success', 'Data karyawan berhasil dinonaktifkan dari daftar.');
    }

    private function cabangs()
    {
        return auth()->user()->hasRole('admin_pusat') ? Cabang::orderBy('nama')->get() : collect();
    }
}
