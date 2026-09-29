<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\Karyawan;
use App\Models\Pelanggan;
use App\Models\SalesOrder;
use App\Models\Survey;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Fase4Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_sales_order_calculates_credit_and_non_eligible_survey_rejects_order(): void
    {
        $user = User::where('email', 'gesi@nexa.com')->firstOrFail();
        $pelanggan = Pelanggan::where('cabang_id', $user->cabang_id)->firstOrFail();
        $barang = Barang::where('kode', 'BRG-KLK-01')->firstOrFail();
        $sales = Karyawan::where('cabang_id', $user->cabang_id)->where('jabatan', 'sales')->firstOrFail();
        $surveyor = Karyawan::where('cabang_id', $user->cabang_id)->where('jabatan', 'surveyor')->firstOrFail();

        $this->actingAs($user)->get(route('sales-order.create'))
            ->assertOk()->assertSee('Ringkasan Skema Kredit')->assertSee('Simpan & Ajukan Survei', false);

        $this->actingAs($user)->post(route('sales-order.store'), [
            'tanggal' => now()->toDateString(),
            'pelanggan_id' => $pelanggan->id,
            'barang_id' => $barang->id,
            'qty' => 1,
            'sales_id' => $sales->id,
            'dp' => 800000,
            'tenor' => 12,
            'bunga_persen' => 1.5,
        ])->assertRedirect();

        $salesOrder = SalesOrder::latest('id')->firstOrFail();
        $this->assertSame('survey', $salesOrder->status);
        $this->assertSame('3200000.00', $salesOrder->pokok_piutang);
        $this->assertSame('576000.00', $salesOrder->total_margin);
        $this->assertSame('3776000.00', $salesOrder->total_kredit);
        $this->assertSame('314667.00', $salesOrder->angsuran_per_bulan);
        $this->assertStringStartsWith('SO-'.now()->format('Ym').'-', $salesOrder->no_so);
        $survey = Survey::where('sales_order_id', $salesOrder->id)->firstOrFail();
        $this->assertSame('menunggu', $survey->hasil);
        $this->get(route('survey.index'))->assertOk()->assertSee($salesOrder->no_so);

        $this->put(route('survey.update', $survey), [
            'surveyor_id' => $surveyor->id,
            'tanggal' => now()->toDateString(),
            'hasil' => 'tidak_layak',
            'catatan' => 'Hasil survei tidak memenuhi kriteria.',
        ])->assertRedirect(route('sales-order.show', $salesOrder));

        $this->assertDatabaseHas('sales_orders', ['id' => $salesOrder->id, 'status' => 'ditolak']);
        $this->assertDatabaseHas('surveys', ['id' => $survey->id, 'surveyor_id' => $surveyor->id, 'hasil' => 'tidak_layak']);
    }
}
