<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\Karyawan;
use App\Models\Pelanggan;
use App\Models\SalesOrder;
use App\Models\SuratJalan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerbaikanPenggunaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_master_forms_render_and_barang_edit_shows_stock_per_branch(): void
    {
        $admin = User::where('email', 'gesi@nexa.com')->firstOrFail();
        $barang = Barang::where('kode', 'BRG-KLK-01')->firstOrFail();

        $this->actingAs($admin)->get(route('pembelian.create'))->assertOk()->assertSee('Pembelian Stok Masuk');
        $this->actingAs($admin)->get(route('karyawan.create'))->assertOk()->assertSee('Tambah Karyawan');
        $this->actingAs($admin)->get(route('barang.index'))->assertOk()->assertSee('Stok per Cabang');
        $this->actingAs($admin)->get(route('barang.edit', $barang))->assertOk()->assertSee('10 unit');

        $this->actingAs($admin)->put(route('barang.update', $barang), [
            'kode' => $barang->kode, 'nama' => $barang->nama, 'merk' => $barang->merk,
            'kategori' => $barang->kategori, 'harga_beli' => $barang->harga_beli, 'harga_jual' => $barang->harga_jual,
            'aktif' => 1, 'stok' => [$admin->cabang_id => 14],
        ])->assertRedirect(route('barang.index'));
        $this->assertDatabaseHas('stok_barangs', ['cabang_id' => $admin->cabang_id, 'barang_id' => $barang->id, 'qty' => 14]);
        $this->assertDatabaseHas('mutasi_stoks', ['cabang_id' => $admin->cabang_id, 'barang_id' => $barang->id, 'tipe' => 'penyesuaian', 'qty' => 4]);
    }

    public function test_branch_admin_can_verify_pending_delivery_and_change_is_logged(): void
    {
        $admin = User::where('email', 'gesi@nexa.com')->firstOrFail();
        $pelanggan = Pelanggan::where('cabang_id', $admin->cabang_id)->firstOrFail();
        $barang = Barang::where('kode', 'BRG-KLK-01')->firstOrFail();
        $sales = Karyawan::where('cabang_id', $admin->cabang_id)->where('jabatan', 'sales')->firstOrFail();
        $so = SalesOrder::create([
            'cabang_id' => $admin->cabang_id, 'no_so' => 'SO-VERIFY-001', 'tanggal' => now()->toDateString(),
            'pelanggan_id' => $pelanggan->id, 'barang_id' => $barang->id, 'qty' => 1, 'sales_id' => $sales->id,
            'harga_jual' => 4000000, 'dp' => 800000, 'tenor' => 12, 'bunga_persen' => 1.5,
            'pokok_piutang' => 3200000, 'total_margin' => 576000, 'total_kredit' => 3776000,
            'angsuran_per_bulan' => 314667, 'status' => 'terkirim', 'created_by' => $admin->id,
        ]);
        $ttb = SuratJalan::create([
            'cabang_id' => $admin->cabang_id, 'sales_order_id' => $so->id, 'no_ttb' => 'TTB-VERIFY-001',
            'tgl_kirim' => now()->toDateString(), 'status_verifikasi' => 'menunggu',
        ]);

        $this->actingAs($admin)->get(route('surat-jalan.index'))->assertOk()->assertSee('Tandai Terverifikasi');
        $this->actingAs($admin)->post(route('surat-jalan.verify', $ttb))->assertRedirect();
        $this->assertDatabaseHas('surat_jalans', ['id' => $ttb->id, 'status_verifikasi' => 'terverifikasi']);
        $this->assertDatabaseHas('activity_log', ['subject_type' => SuratJalan::class, 'subject_id' => $ttb->id, 'log_name' => 'surat_jalan']);
    }
}
