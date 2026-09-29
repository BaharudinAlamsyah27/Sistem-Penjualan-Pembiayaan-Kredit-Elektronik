<?php

namespace Tests\Feature;

use App\Models\Angsuran;
use App\Models\Barang;
use App\Models\Faktur;
use App\Models\Karyawan;
use App\Models\Pelanggan;
use App\Models\SalesOrder;
use App\Models\User;
use App\Services\LabaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Fase7Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_reports_export_data_and_profit_uses_paid_installment_margin(): void
    {
        $admin = User::where('email', 'admin@nexa.com')->firstOrFail();
        $branchUser = User::where('email', 'gesi@nexa.com')->firstOrFail();
        $customer = Pelanggan::where('cabang_id', $branchUser->cabang_id)->firstOrFail();
        $product = Barang::where('kode', 'BRG-KLK-01')->firstOrFail();
        $sales = Karyawan::where('cabang_id', $branchUser->cabang_id)->where('jabatan', 'sales')->firstOrFail();
        $collector = Karyawan::where('cabang_id', $branchUser->cabang_id)->where('jabatan', 'kolektor')->firstOrFail();
        $so = SalesOrder::create([
            'cabang_id' => $branchUser->cabang_id, 'no_so' => 'SO-F7-001', 'tanggal' => now()->toDateString(),
            'pelanggan_id' => $customer->id, 'barang_id' => $product->id, 'qty' => 1, 'sales_id' => $sales->id,
            'harga_jual' => 4000000, 'dp' => 800000, 'tenor' => 12, 'bunga_persen' => 1.5,
            'pokok_piutang' => 3200000, 'total_margin' => 576000, 'total_kredit' => 3776000,
            'angsuran_per_bulan' => 314667, 'status' => 'terkirim', 'created_by' => $branchUser->id,
        ]);
        $faktur = Faktur::create([
            'cabang_id' => $branchUser->cabang_id, 'sales_order_id' => $so->id, 'no_faktur' => 'INV-F7-001',
            'tanggal' => now()->toDateString(), 'tgl_dp' => now()->toDateString(), 'status' => 'aktif',
        ]);
        Angsuran::create([
            'cabang_id' => $branchUser->cabang_id, 'faktur_id' => $faktur->id, 'jenis' => 'angsuran',
            'angsuran_ke' => 1, 'nominal' => 314667, 'jatuh_tempo' => now()->toDateString(),
            'kolektor_id' => $collector->id, 'status' => 'lunas', 'tgl_bayar' => now()->toDateString(), 'nominal_dibayar' => 314667,
        ]);

        $this->assertDatabaseHas('sales_orders', ['no_so' => 'SO-F7-001', 'status' => 'terkirim']);
        $this->assertEqualsWithDelta(48000, LabaService::bulanBerjalan($branchUser->cabang_id)['nominal'], 1);
        $this->actingAs($admin)->get(route('laporan.penjualan', [
            'dari' => now()->startOfMonth()->toDateString(), 'sampai' => now()->toDateString(), 'cabang' => $branchUser->cabang_id,
        ]))->assertOk()->assertSee($sales->nama)->assertSee('Rp 4.000.000');
        $this->actingAs($admin)->get(route('laporan.kolektor', [
            'dari' => now()->startOfMonth()->toDateString(), 'sampai' => now()->toDateString(), 'cabang' => $branchUser->cabang_id,
        ]))->assertOk()->assertSee($collector->nama)->assertSee('Rp 314.667');
        $this->actingAs($admin)->get(route('laporan.export', [
            'jenis' => 'penjualan', 'dari' => now()->startOfMonth()->toDateString(), 'sampai' => now()->toDateString(),
            'cabang' => $branchUser->cabang_id,
        ]))->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8')->assertDownload('laporan-penjualan-'.now()->startOfMonth()->toDateString().'-'.now()->toDateString().'.csv');
        $this->actingAs($admin)->get(route('laporan.laba-rugi'))->assertOk()->assertSee('Margin diterima bulan ini');
    }
}
