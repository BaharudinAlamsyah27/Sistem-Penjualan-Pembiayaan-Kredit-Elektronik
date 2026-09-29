<?php

namespace App\Http\Controllers;

use App\Http\Requests\SalesOrderRequest;
use App\Models\Barang;
use App\Models\Cabang;
use App\Models\Karyawan;
use App\Models\Pelanggan;
use App\Models\SalesOrder;
use App\Models\Survey;
use App\Services\KreditService;
use App\Services\NomorDokumenService;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SalesOrderController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(SalesOrder::class, 'sales_order');
    }

    public function index(): View
    {
        return view('sales-order.index', [
            'salesOrders' => SalesOrder::with(['pelanggan', 'barang', 'sales', 'survey'])
                ->latest('tanggal')->paginate(15),
        ]);
    }

    public function create(): View
    {
        $isAdminPusat = auth()->user()->hasRole('admin_pusat');
        $cabangId = $isAdminPusat ? request()->integer('cabang_id') : auth()->user()->cabang_id;

        return view('sales-order.create', [
            'isAdminPusat' => $isAdminPusat,
            'cabangs' => $isAdminPusat ? Cabang::where('aktif', true)->orderBy('nama')->get() : collect(),
            'cabangTerpilih' => $cabangId,
            'pelanggans' => Pelanggan::where('cabang_id', $cabangId)->orderBy('nama')->get(),
            'barangs' => Barang::where('aktif', true)->orderBy('nama')->get(),
            'sales' => Karyawan::where('cabang_id', $cabangId)->where('jabatan', 'sales')->where('aktif', true)->orderBy('nama')->get(),
        ]);
    }

    public function store(SalesOrderRequest $request, KreditService $kreditService, NomorDokumenService $nomorDokumenService)
    {
        $data = $request->validated();
        $barang = Barang::findOrFail($data['barang_id']);
        $hargaTotal = (float) $barang->harga_jual * (int) $data['qty'];
        $kredit = $kreditService::hitung($hargaTotal, (float) $data['dp'], (int) $data['tenor'], (float) $data['bunga_persen']);

        $salesOrder = DB::transaction(function () use ($data, $barang, $kredit, $nomorDokumenService) {
            $salesOrder = SalesOrder::create([
                'cabang_id' => $data['cabang_id'],
                'no_so' => $nomorDokumenService->berikutnya((int) $data['cabang_id'], 'SO', $data['tanggal']),
                'tanggal' => $data['tanggal'],
                'pelanggan_id' => $data['pelanggan_id'],
                'barang_id' => $barang->id,
                'qty' => $data['qty'],
                'sales_id' => $data['sales_id'],
                'harga_jual' => $barang->harga_jual,
                'dp' => $data['dp'],
                'tenor' => $data['tenor'],
                'bunga_persen' => $data['bunga_persen'],
                'pokok_piutang' => $kredit['pokok_piutang'],
                'total_margin' => $kredit['total_margin'],
                'total_kredit' => $kredit['total_kredit'],
                'angsuran_per_bulan' => $kredit['angsuran_per_bulan'],
                'status' => 'survey',
                'created_by' => auth()->id(),
            ]);

            Survey::create([
                'cabang_id' => $salesOrder->cabang_id,
                'sales_order_id' => $salesOrder->id,
                'hasil' => 'menunggu',
            ]);

            return $salesOrder;
        });

        return redirect()->route('sales-order.show', $salesOrder)->with('success', 'Sales Order berhasil dibuat dan menunggu hasil survei.');
    }

    public function show(SalesOrder $sales_order): View
    {
        $sales_order->load(['pelanggan', 'barang', 'sales', 'survey.surveyor']);

        return view('sales-order.show', ['salesOrder' => $sales_order]);
    }
}
