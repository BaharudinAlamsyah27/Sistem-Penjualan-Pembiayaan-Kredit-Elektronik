<?php

namespace Tests\Feature;

use App\Models\Angsuran;
use App\Models\Barang;
use App\Models\Faktur;
use App\Models\Karyawan;
use App\Models\Pelanggan;
use App\Models\SalesOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Fase6Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_payment_is_recorded_and_completing_schedule_closes_invoice(): void
    {
        $user = User::where('email', 'gesi@nexa.com')->firstOrFail();
        $pelanggan = Pelanggan::where('cabang_id', $user->cabang_id)->firstOrFail();
        $barang = Barang::where('kode', 'BRG-KLK-01')->firstOrFail();
        $sales = Karyawan::where('cabang_id', $user->cabang_id)->where('jabatan', 'sales')->firstOrFail();
        $kolektor = Karyawan::where('cabang_id', $user->cabang_id)->where('jabatan', 'kolektor')->firstOrFail();

        $so = SalesOrder::create([
            'cabang_id' => $user->cabang_id, 'no_so' => 'SO-F6-001', 'tanggal' => now()->toDateString(),
            'pelanggan_id' => $pelanggan->id, 'barang_id' => $barang->id, 'qty' => 1, 'sales_id' => $sales->id,
            'harga_jual' => 4000000, 'dp' => 0, 'tenor' => 1, 'bunga_persen' => 0,
            'pokok_piutang' => 4000000, 'total_margin' => 0, 'total_kredit' => 4000000,
            'angsuran_per_bulan' => 4000000, 'status' => 'disetujui', 'created_by' => $user->id,
        ]);
        $faktur = Faktur::create([
            'cabang_id' => $user->cabang_id, 'sales_order_id' => $so->id, 'no_faktur' => 'INV-F6-001',
            'tanggal' => now()->toDateString(), 'tgl_dp' => now()->toDateString(), 'status' => 'aktif',
        ]);
        $angsuran = Angsuran::create([
            'cabang_id' => $user->cabang_id, 'faktur_id' => $faktur->id, 'jenis' => 'angsuran',
            'angsuran_ke' => 1, 'nominal' => 4000000, 'jatuh_tempo' => now()->toDateString(), 'status' => 'belum',
        ]);

        $this->actingAs($user)->post(route('angsuran.store', $angsuran), [
            'tgl_bayar' => now()->toDateString(), 'metode' => 'tunai', 'kolektor_id' => $kolektor->id,
        ])->assertRedirect();

        $this->assertDatabaseHas('angsurans', [
            'id' => $angsuran->id, 'status' => 'lunas', 'nominal_dibayar' => '4000000.00',
            'kolektor_id' => $kolektor->id, 'dicatat_oleh' => $user->id,
        ]);
        $this->assertSame('lunas', $faktur->fresh()->status);
        $this->actingAs($user)->put(route('angsuran.update', $angsuran), [
            'tgl_bayar' => now()->toDateString(), 'metode' => 'transfer',
        ])->assertForbidden();
        $adminPusat = User::where('email', 'admin@nexa.com')->firstOrFail();
        $this->actingAs($adminPusat)->put(route('angsuran.update', $angsuran), [
            'tgl_bayar' => now()->toDateString(), 'metode' => 'transfer',
        ])->assertRedirect();
        $this->assertDatabaseHas('activity_log', ['subject_type' => Angsuran::class, 'subject_id' => $angsuran->id, 'log_name' => 'angsuran']);
        $this->actingAs($user)->get(route('laporan.tagihan', ['bulan' => now()->format('Y-m'), 'status' => 'lunas']))
            ->assertOk()->assertSee('INV-F6-001')->assertSee($pelanggan->nama);
    }
}
