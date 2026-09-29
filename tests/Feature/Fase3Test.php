<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\Pelanggan;
use App\Models\StokBarang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Fase3Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_admin_cabang_only_sees_and_creates_records_for_its_branch(): void
    {
        $admin = User::where('email', 'gesi@nexa.com')->firstOrFail();
        $otherBranchCustomer = Pelanggan::where('kode', 'PLG-GM-001')->firstOrFail();

        $this->actingAs($admin)->get(route('pelanggan.index'))->assertOk()->assertDontSee($otherBranchCustomer->nama);
        $this->actingAs($admin)->post(route('pelanggan.store'), [
            'kode' => 'PLG-GS-NEW', 'nama' => 'Pelanggan Cabang Gesi', 'alamat' => 'Jl. Baru',
            'kabupaten' => 'Sragen', 'kecamatan' => 'Gesi',
        ])->assertRedirect(route('pelanggan.index'));

        $this->assertDatabaseHas('pelanggans', ['kode' => 'PLG-GS-NEW', 'cabang_id' => $admin->cabang_id]);
    }

    public function test_admin_cabang_cannot_access_cabang_management(): void
    {
        $admin = User::where('email', 'gesi@nexa.com')->firstOrFail();
        $this->actingAs($admin)->get(route('cabang.index'))->assertForbidden();
    }

    public function test_pembelian_adds_stock_and_records_mutation(): void
    {
        $admin = User::where('email', 'gesi@nexa.com')->firstOrFail();
        $barang = Barang::where('kode', 'BRG-KLK-01')->firstOrFail();
        $before = StokBarang::where('cabang_id', $admin->cabang_id)->where('barang_id', $barang->id)->value('qty');

        $this->actingAs($admin)->post(route('pembelian.store'), [
            'tanggal' => now()->toDateString(), 'supplier' => 'PT Elektronik', 'metode' => 'cashmoney',
            'items' => [['barang_id' => $barang->id, 'qty' => 3, 'harga' => '3200000.00']],
        ])->assertRedirect(route('pembelian.index'))->assertSessionHasNoErrors();

        $this->assertDatabaseHas('stok_barangs', ['cabang_id' => $admin->cabang_id, 'barang_id' => $barang->id, 'qty' => $before + 3]);
        $this->assertDatabaseHas('mutasi_stoks', ['cabang_id' => $admin->cabang_id, 'barang_id' => $barang->id, 'tipe' => 'masuk', 'qty' => 3]);
        $this->assertDatabaseHas('pembelian_details', ['barang_id' => $barang->id, 'qty' => 3, 'harga' => '3200000.00']);
    }

    public function test_admin_pusat_must_choose_branch_for_employee_and_customer(): void
    {
        $admin = User::where('email', 'admin@nexa.com')->firstOrFail();
        $this->actingAs($admin)->post(route('karyawan.store'), ['kode' => 'KRY-X', 'nama' => 'Petugas', 'jabatan' => 'driver'])->assertSessionHasErrors('cabang_id');
        $this->actingAs($admin)->post(route('pelanggan.store'), ['kode' => 'PLG-X', 'nama' => 'Calon', 'alamat' => 'Jl. X', 'kabupaten' => 'Sragen', 'kecamatan' => 'Gesi'])->assertSessionHasErrors('cabang_id');
    }
}
