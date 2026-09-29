<?php

namespace App\Http\Controllers;

use App\Http\Requests\TemplateKwitansiRequest;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class TemplateKwitansiController extends Controller
{
    private const SETTING_KEY = 'template_kwitansi_path';

    public function index(Request $request): View
    {
        $this->authorizeAdminPusat($request);
        $path = Setting::get(self::SETTING_KEY);

        return view('pengaturan.template-kwitansi', [
            'templateTersedia' => is_string($path) && Storage::disk('local')->exists($path),
        ]);
    }

    public function upload(TemplateKwitansiRequest $request): RedirectResponse
    {
        $oldPath = Setting::get(self::SETTING_KEY);
        $file = $request->file('template');
        $extension = $file->guessExtension();
        abort_unless(in_array($extension, ['jpg', 'jpeg', 'png'], true), 422, 'Format gambar tidak didukung.');
        $path = $file->storeAs('template-cetak', 'kwitansi-'.str()->uuid().'.'.$extension, 'local');

        try {
            Setting::set(self::SETTING_KEY, $path);
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($path);
            throw $exception;
        }

        if (is_string($oldPath) && $oldPath !== '' && $oldPath !== $path) {
            Storage::disk('local')->delete($oldPath);
        }

        return to_route('pengaturan.template-kwitansi.index')->with('success', 'Template kwitansi berhasil diunggah.');
    }

    public function preview(Request $request): BinaryFileResponse
    {
        $this->authorizeAdminPusat($request);
        $path = Setting::get(self::SETTING_KEY);
        abort_unless(is_string($path) && Storage::disk('local')->exists($path), 404);

        return response()->file(Storage::disk('local')->path($path), [
            'Content-Type' => Storage::disk('local')->mimeType($path) ?? 'application/octet-stream',
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function destroy(Request $request): RedirectResponse
    {
        $this->authorizeAdminPusat($request);
        $path = Setting::get(self::SETTING_KEY);
        Setting::query()->where('key', self::SETTING_KEY)->delete();

        if (is_string($path) && $path !== '') {
            Storage::disk('local')->delete($path);
        }

        return to_route('pengaturan.template-kwitansi.index')->with('success', 'Template kwitansi berhasil dihapus.');
    }

    private function authorizeAdminPusat(Request $request): void
    {
        abort_unless($request->user()?->hasRole('admin_pusat'), 403);
    }
}
