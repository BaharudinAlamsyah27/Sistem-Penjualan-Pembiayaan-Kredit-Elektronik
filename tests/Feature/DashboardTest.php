<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_admin_pusat_sees_dashboard_analytics_and_cabang_menu(): void
    {
        /** @var User $adminPusat */
        $adminPusat = User::where('email', 'admin@nexa.com')->first();

        $response = $this->actingAs($adminPusat)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('NexaERP');
        $response->assertSee('Dashboard Analytics');
        $response->assertSee('Super Admin');
        $response->assertSee('Data Cabang');
        $response->assertSee('Laba Bersih (Bulan Ini)');
        $response->assertSee('Total Piutang Berjalan');
        $response->assertSee('Angsuran Masuk Hari Ini');
        $response->assertSee('Sales Order Menunggu Survey');
        $response->assertSee('Tagihan Angsuran Jatuh Tempo Hari Ini');
        $response->assertSee('Bpk. Sudirman');
        $response->assertSee('Ibu Siti Aminah');
    }

    public function test_admin_cabang_sees_branch_badge_and_no_cabang_menu(): void
    {
        /** @var User $adminSolo */
        $adminSolo = User::where('email', 'solo@nexa.com')->first();

        $response = $this->actingAs($adminSolo)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('NexaERP');
        $response->assertSee('Cabang Solo');
        $response->assertDontSee('Data Cabang');

        // Admin cabang cannot access /cabang route
        $cabangResponse = $this->actingAs($adminSolo)->get('/cabang');
        $cabangResponse->assertStatus(403);
    }

    public function test_authenticated_user_can_access_sidebar_modules(): void
    {
        /** @var User $adminSolo */
        $adminSolo = User::where('email', 'solo@nexa.com')->first();

        $this->actingAs($adminSolo)->get('/pelanggan')->assertStatus(200)->assertSee('Data Pelanggan');
        $this->actingAs($adminSolo)->get('/sales-order')->assertStatus(200)->assertSee('Sales Order (SO)');
        $this->actingAs($adminSolo)->get('/survey')->assertStatus(200)->assertSee('Survey Kelayakan');
        $this->actingAs($adminSolo)->get('/angsuran')->assertStatus(200)->assertSee('Penerimaan Angsuran');
        $this->actingAs($adminSolo)->get('/barang')->assertStatus(200)->assertSee('Data Barang');
    }
}
