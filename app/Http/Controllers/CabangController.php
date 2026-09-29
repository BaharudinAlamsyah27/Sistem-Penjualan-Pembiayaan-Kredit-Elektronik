<?php

namespace App\Http\Controllers;

use App\Http\Requests\CabangRequest;
use App\Models\Cabang;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CabangController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Cabang::class, 'cabang');
    }

    public function index(): View
    {
        return view('master.cabang.index', ['cabangs' => Cabang::orderBy('nama')->paginate(15)]);
    }

    public function create(): View
    {
        return view('master.cabang.form', ['cabang' => new Cabang]);
    }

    public function store(CabangRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['aktif'] = $request->boolean('aktif');
        Cabang::create($data);

        return redirect()->route('cabang.index')->with('success', 'Data cabang berhasil ditambahkan.');
    }

    public function edit(Cabang $cabang): View
    {
        return view('master.cabang.form', compact('cabang'));
    }

    public function update(CabangRequest $request, Cabang $cabang): RedirectResponse
    {
        $data = $request->validated();
        $data['aktif'] = $request->boolean('aktif');
        $cabang->update($data);

        return redirect()->route('cabang.index')->with('success', 'Data cabang berhasil diperbarui.');
    }

    public function destroy(Cabang $cabang): RedirectResponse
    {
        if ($cabang->users()->exists() || $cabang->karyawans()->exists() || $cabang->pelanggans()->exists() || $cabang->stokBarangs()->exists() || $cabang->salesOrders()->exists() || $cabang->fakturs()->exists()) {
            return back()->with('error', 'Cabang yang masih memiliki data operasional tidak dapat dihapus.');
        }
        $cabang->delete();

        return redirect()->route('cabang.index')->with('success', 'Data cabang berhasil dihapus.');
    }
}
