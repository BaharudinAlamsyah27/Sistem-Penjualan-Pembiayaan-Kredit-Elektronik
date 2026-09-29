<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TemplateKwitansiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Storage::fake('local');
    }

    public function test_admin_pusat_can_upload_preview_replace_and_remove_private_receipt_template(): void
    {
        $admin = User::where('email', 'admin@nexa.com')->firstOrFail();
        $branchAdmin = User::where('email', 'gesi@nexa.com')->firstOrFail();
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/Zb8AAAAASUVORK5CYII=');

        $this->actingAs($branchAdmin)->get(route('pengaturan.template-kwitansi.index'))->assertForbidden();
        $this->actingAs($admin)->post(route('pengaturan.template-kwitansi.upload'), [
            'template' => UploadedFile::fake()->createWithContent('kwitansi.png', $png),
        ])->assertRedirect(route('pengaturan.template-kwitansi.index'));

        $oldPath = Setting::get('template_kwitansi_path');
        $this->assertIsString($oldPath);
        Storage::disk('local')->assertExists($oldPath);
        $this->actingAs($admin)->get(route('pengaturan.template-kwitansi.preview'))->assertOk();
        $this->actingAs($admin)->get(route('pengaturan.template-kwitansi.kalibrasi', ['grid' => 1]))
            ->assertOk()->assertSee('PRATINJAU KALIBRASI')->assertSee('background-size:10mm 10mm', false);
        $this->actingAs($branchAdmin)->get(route('pengaturan.template-kwitansi.kalibrasi'))->assertForbidden();

        $this->actingAs($admin)->post(route('pengaturan.template-kwitansi.upload'), [
            'template' => UploadedFile::fake()->createWithContent('kwitansi-baru.png', $png),
        ])->assertRedirect();
        $newPath = Setting::get('template_kwitansi_path');
        $this->assertNotSame($oldPath, $newPath);
        Storage::disk('local')->assertMissing($oldPath);

        $this->actingAs($admin)->delete(route('pengaturan.template-kwitansi.destroy'))->assertRedirect();
        $this->assertNull(Setting::get('template_kwitansi_path'));
        Storage::disk('local')->assertMissing($newPath);
    }
}
