<?php

namespace Tests\Feature;

use App\Models\Cabang;
use App\Models\Karyawan;
use App\Models\Pelanggan;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class Fase1Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_setting_helper_and_model_works(): void
    {
        $this->assertEquals('0', setting('dp_hitung_angsuran_pertama'));

        Setting::set('test_key', 'test_value');
        $this->assertEquals('test_value', setting('test_key'));
        $this->assertEquals('default', setting('non_existent_key', 'default'));
    }

    public function test_admin_pusat_can_login(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin@nexa.com',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));

        /** @var User $user */
        $user = auth()->user();
        $this->assertTrue($user->hasRole('admin_pusat'));
        $this->assertNull($user->cabang_id);
    }

    public function test_admin_cabang_can_login(): void
    {
        $response = $this->post('/login', [
            'email' => 'gesi@nexa.com',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));

        /** @var User $user */
        $user = auth()->user();
        $this->assertTrue($user->hasRole('admin_cabang'));
        $this->assertNotNull($user->cabang_id);
    }

    public function test_pelanggan_no_ktp_is_encrypted_in_database(): void
    {
        $pelanggan = Pelanggan::withoutGlobalScopes()->where('nama', 'Ahmad Riyadi')->first();
        $this->assertNotNull($pelanggan);

        // Through Eloquent getter, it is decrypted
        $this->assertEquals('3372010101900001', $pelanggan->no_ktp);

        // Directly via DB query, it is encrypted ciphertext
        $raw = DB::table('pelanggans')->where('id', $pelanggan->id)->value('no_ktp');
        $this->assertNotEquals('3372010101900001', $raw);
        $this->assertStringStartsWith('eyJ', $raw); // Laravel encrypted payload JSON base64
    }

    public function test_admin_cabang_cannot_see_other_branch_data(): void
    {
        $adminGesi = User::where('email', 'gesi@nexa.com')->first();
        $cabangGesi = Cabang::where('kode', 'CAB-GESI')->first();
        $cabangGemolong = Cabang::where('kode', 'CAB-GEMOLONG')->first();

        $this->actingAs($adminGesi);

        // Gesi admin should only see Gesi customers
        $pelanggans = Pelanggan::all();
        $this->assertGreaterThan(0, $pelanggans->count());
        foreach ($pelanggans as $p) {
            $this->assertEquals($cabangGesi->id, $p->cabang_id);
            $this->assertNotEquals($cabangGemolong->id, $p->cabang_id);
        }

        // Gesi admin should only see Gesi employees
        $karyawans = Karyawan::all();
        $this->assertGreaterThan(0, $karyawans->count());
        foreach ($karyawans as $k) {
            $this->assertEquals($cabangGesi->id, $k->cabang_id);
            $this->assertNotEquals($cabangGemolong->id, $k->cabang_id);
        }
    }

    public function test_admin_pusat_can_see_all_branches_data(): void
    {
        $adminPusat = User::where('email', 'admin@nexa.com')->first();
        $this->actingAs($adminPusat);

        $pelanggans = Pelanggan::all();
        $cabangIds = $pelanggans->pluck('cabang_id')->unique();

        // Admin Pusat should see data from more than 1 branch
        $this->assertGreaterThan(1, $cabangIds->count());
    }

    public function test_branch_id_is_automatically_assigned_on_create_for_admin_cabang(): void
    {
        $adminGesi = User::where('email', 'gesi@nexa.com')->first();
        $cabangGesi = Cabang::where('kode', 'CAB-GESI')->first();

        $this->actingAs($adminGesi);

        $baru = Pelanggan::create([
            'kode' => 'PLG-TEST-AUTO',
            'nama' => 'Pelanggan Uji Otomatis',
            'no_hp' => '0899999999',
            'no_ktp' => '3372099999999999',
            'alamat' => 'Alamat Uji',
            'kabupaten' => 'Kota Surakarta',
            'kecamatan' => 'Laweyan',
        ]);

        $this->assertEquals($cabangGesi->id, $baru->cabang_id);
    }
}
