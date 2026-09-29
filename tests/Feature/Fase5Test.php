<?php

namespace Tests\Feature;

use App\Models\Angsuran;
use App\Models\Barang;
use App\Models\Karyawan;
use App\Models\Pelanggan;
use App\Models\SalesOrder;
use App\Models\Setting;
use App\Models\StokBarang;
use App\Models\Survey;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Fase5Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_invoice_creates_exact_schedule_and_reduces_stock_then_marks_delivery(): void
    {
        [$user, $salesOrder, $barang] = $this->siapkanOrderLayak();
        $driver = Karyawan::where('cabang_id', $user->cabang_id)->where('jabatan', 'driver')->firstOrFail();
        $stok = StokBarang::where('cabang_id', $user->cabang_id)->where('barang_id', $barang->id)->firstOrFail();
        $stokAwal = $stok->qty;

        $this->actingAs($user)->post(route('sales-order.faktur.store', $salesOrder), [
            'tgl_dp' => '2026-01-31',
            'tgl_kirim' => '2026-02-05',
            'driver_id' => $driver->id,
            'nota' => 'NOTA-01',
        ])->assertRedirect(route('sales-order.show', $salesOrder));

        $faktur = $salesOrder->faktur()->firstOrFail();
        $angsuran = $faktur->angsurans()->where('jenis', 'angsuran')->orderBy('angsuran_ke')->get();
        $this->assertSame(12, $angsuran->count());
        $this->assertSame(377600000, (int) round((float) $angsuran->sum('nominal') * 100));
        $this->assertSame('314663.00', $angsuran->last()->nominal);
        $this->assertSame('2026-02-28', $angsuran->first()->jatuh_tempo->toDateString());
        $this->assertSame('2027-01-31', $angsuran->last()->jatuh_tempo->toDateString());
        $this->assertDatabaseHas('angsurans', ['faktur_id' => $faktur->id, 'jenis' => 'dp', 'angsuran_ke' => 0, 'status' => 'lunas', 'nominal' => '800000.00']);
        $this->assertDatabaseHas('stok_barangs', ['id' => $stok->id, 'qty' => $stokAwal - 1]);
        $this->assertDatabaseHas('mutasi_stoks', ['cabang_id' => $user->cabang_id, 'barang_id' => $barang->id, 'tipe' => 'keluar', 'qty' => 1]);
        $this->assertSame('disetujui', $salesOrder->fresh()->status);

        $this->actingAs($user)->post(route('sales-order.terkirim', $salesOrder))->assertRedirect(route('sales-order.show', $salesOrder));
        $this->assertSame('terkirim', $salesOrder->fresh()->status);
    }

    public function test_insufficient_stock_leaves_order_without_invoice_or_schedule(): void
    {
        [$user, $salesOrder, $barang] = $this->siapkanOrderLayak();
        StokBarang::where('cabang_id', $user->cabang_id)->where('barang_id', $barang->id)->update(['qty' => 0]);
        $driver = Karyawan::where('cabang_id', $user->cabang_id)->where('jabatan', 'driver')->firstOrFail();

        $this->actingAs($user)->from(route('sales-order.faktur.create', $salesOrder))
            ->post(route('sales-order.faktur.store', $salesOrder), [
                'tgl_dp' => now()->toDateString(), 'tgl_kirim' => now()->toDateString(), 'driver_id' => $driver->id,
            ])->assertSessionHasErrors('stok');

        $this->assertDatabaseCount('fakturs', 0);
        $this->assertDatabaseCount('angsurans', 0);
        $this->assertDatabaseCount('surat_jalans', 0);
        $this->assertSame('survey', $salesOrder->fresh()->status);
    }

    private function siapkanOrderLayak(): array
    {
        $user = User::where('email', 'gesi@nexa.com')->firstOrFail();
        $pelanggan = Pelanggan::where('cabang_id', $user->cabang_id)->firstOrFail();
        $barang = Barang::where('kode', 'BRG-KLK-01')->firstOrFail();
        $sales = Karyawan::where('cabang_id', $user->cabang_id)->where('jabatan', 'sales')->firstOrFail();

        $salesOrder = SalesOrder::create([
            'cabang_id' => $user->cabang_id, 'no_so' => 'SO-TEST-001', 'tanggal' => now()->toDateString(),
            'pelanggan_id' => $pelanggan->id, 'barang_id' => $barang->id, 'qty' => 1, 'sales_id' => $sales->id,
            'harga_jual' => '4000000.00', 'dp' => '800000.00', 'tenor' => 12, 'bunga_persen' => '1.50',
            'pokok_piutang' => '3200000.00', 'total_margin' => '576000.00', 'total_kredit' => '3776000.00',
            'angsuran_per_bulan' => '314667.00', 'status' => 'survey', 'created_by' => $user->id,
        ]);

        Survey::create([
            'cabang_id' => $user->cabang_id, 'sales_order_id' => $salesOrder->id,
            'hasil' => 'layak', 'tanggal' => now()->toDateString(),
        ]);

        Setting::set('dp_hitung_angsuran_pertama', '0');

        return [$user, $salesOrder, $barang];
    }
}
