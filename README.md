# NexaERP

NexaERP adalah aplikasi ERP internal untuk penjualan barang elektronik secara kredit, stok per cabang, survei pelanggan, penerimaan angsuran, laporan, dan cetak dokumen.

## Mulai lokal (Laragon)

1. Pastikan PHP 8.2+, Composer, Node.js LTS, dan MySQL aktif.
2. Salin `.env.example` menjadi `.env`, isi konfigurasi MySQL lokal, lalu jalankan `composer install`.
3. Jalankan `php artisan key:generate`.
4. Jalankan migration dan seeder hanya pada database lokal kosong: `php artisan migrate:fresh --seed`.
5. Jalankan `npm ci`, lalu `npm run build` (atau `npm run dev` saat mengembangkan CSS/JS).
6. Buka proyek melalui Laragon atau `php artisan serve`.

> `migrate:fresh` menghapus seluruh tabel database yang dikonfigurasi. Jangan jalankan pada database berisi data penting atau produksi.

## Dokumen proyek

- [Superbook pengguna dan teknis](docs/SUPERBOOK-NEXAERP.md)
- [PDF Superbook](output/pdf/Superbook-NexaERP.pdf)
- [Panduan pengguna singkat](docs/PANDUAN.md)
- [Panduan deploy cPanel](docs/DEPLOY-CPANEL.md)
- [Status fase dan keputusan](docs/PROGRESS.md)
- `AGENTS.md` adalah aturan proyek dan sumber kebenaran kebutuhan.

## Verifikasi perubahan

Jalankan test fokus yang sesuai dengan perubahan, misalnya `php artisan test --compact tests/Feature/Fase5Test.php`. Pastikan test memakai database khusus test, bukan database Laragon/produksi.

## Catatan teknis

Versi dependency backend di `composer.json` saat ini Laravel `^12.0`, meskipun spesifikasi awal menyebut Laravel 11. Jangan menurunkan/menaikkan versi framework tanpa menilai kompatibilitas dependency dan hosting.
