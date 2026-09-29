<?php

namespace App\Http\Controllers;

use App\Http\Requests\PenerimaanAngsuranRequest;
use App\Models\Angsuran;
use App\Models\Cabang;
use App\Models\Karyawan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PenerimaanAngsuranController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Angsuran::class);
        $user = $request->user();
        $query = Angsuran::query()->with(['faktur.salesOrder.pelanggan', 'kolektor'])
            ->where('jenis', 'angsuran')
            ->when($request->filled('q'), function ($builder) use ($request) {
                $term = '%'.$request->string('q')->trim().'%';
                $builder->whereHas('faktur', fn ($q) => $q->where('no_faktur', 'like', $term)
                    ->orWhereHas('salesOrder.pelanggan', fn ($pelanggan) => $pelanggan->where('nama', 'like', $term)));
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->query('status')))
            ->when($user->hasRole('admin_pusat') && $request->filled('cabang'), fn ($q) => $q->where('cabang_id', $request->integer('cabang')))
            ->orderBy('jatuh_tempo')->orderBy('faktur_id')->orderBy('angsuran_ke');

        return view('angsuran.index', [
            'angsurans' => $query->paginate(15)->withQueryString(),
            'kolektors' => Karyawan::query()->where('jabatan', 'kolektor')->where('aktif', true)->orderBy('nama')->get(),
            'cabangs' => $user->hasRole('admin_pusat') ? Cabang::orderBy('nama')->get() : collect(),
        ]);
    }

    public function store(PenerimaanAngsuranRequest $request, Angsuran $angsuran): RedirectResponse
    {
        $this->authorize('receive', $angsuran);

        DB::transaction(function () use ($request, $angsuran) {
            $locked = Angsuran::query()->whereKey($angsuran->id)->lockForUpdate()->firstOrFail();
            if ($locked->jenis !== 'angsuran' || $locked->status === 'lunas') {
                throw ValidationException::withMessages(['angsuran' => 'Angsuran ini sudah lunas atau tidak dapat dibayar.']);
            }

            $data = $request->validated();
            $locked->update([
                'status' => 'lunas',
                'tgl_bayar' => $data['tgl_bayar'],
                'nominal_dibayar' => $locked->nominal,
                'metode' => $data['metode'],
                'kolektor_id' => $data['kolektor_id'] ?? null,
                'catatan' => $data['catatan'] ?? null,
                'dicatat_oleh' => $request->user()->id,
            ]);

            $faktur = $locked->faktur()->lockForUpdate()->firstOrFail();
            if (! $faktur->angsurans()->where('jenis', 'angsuran')->where('status', '!=', 'lunas')->exists()) {
                $faktur->update(['status' => 'lunas']);
            }
        });

        return back()->with('success', 'Pembayaran angsuran berhasil dicatat.');
    }

    public function update(PenerimaanAngsuranRequest $request, Angsuran $angsuran): RedirectResponse
    {
        $this->authorize('update', $angsuran);

        DB::transaction(function () use ($request, $angsuran) {
            $locked = Angsuran::query()->whereKey($angsuran->id)->lockForUpdate()->firstOrFail();
            if ($locked->status !== 'lunas') {
                throw ValidationException::withMessages(['angsuran' => 'Koreksi hanya berlaku untuk angsuran yang sudah lunas.']);
            }

            $data = $request->validated();
            $locked->update([
                'tgl_bayar' => $data['tgl_bayar'],
                'metode' => $data['metode'],
                'kolektor_id' => $data['kolektor_id'] ?? null,
                'catatan' => $data['catatan'] ?? null,
                'dicatat_oleh' => $request->user()->id,
            ]);
        });

        return back()->with('success', 'Koreksi pembayaran berhasil disimpan dan dicatat.');
    }
}
