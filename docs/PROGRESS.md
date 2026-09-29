# NexaERP — Catatan Kemajuan Proyek

> File ini diperbarui otomatis oleh agent di akhir setiap fase.

---

## Status Fase

| Fase | Judul | Status | Tanggal Selesai |
|------|-------|--------|----------------|
| 0 | Persiapan | ✅ Selesai | 2026-09-29 |
| 1 | Database, Seeder, Peran | ✅ Selesai | 2026-09-29 |
| 2 | Layout & Dashboard | ⏳ Belum dimulai | — |
| 3 | Data Master | ⏳ Belum dimulai | — |
| 4 | Sales Order & Survei | ⏳ Belum dimulai | — |
| 5 | Faktur & Jadwal Angsuran | ⏳ Belum dimulai | — |
| 6 | Penerimaan Angsuran & Tagihan | ⏳ Belum dimulai | — |
| 7 | Laporan Dasar | ⏳ Belum dimulai | — |
| 8 | Cetak | ⏳ Belum dimulai | — |
| 9 | Pengerasan & Deploy | ⏳ Belum dimulai | — |

---

## Fase 0 — Persiapan

### Tugas
- [x] Laravel 12.69.2 terinstall (kompatibel dengan panduan Laravel 11)
- [x] .env dikonfigurasi: MySQL Laragon, APP_LOCALE=id, APP_TIMEZONE=Asia/Jakarta
- [x] laravel/breeze v2.4.2 terinstall & scaffold (Blade stack)
- [x] spatie/laravel-permission v6.25.0 terinstall
- [x] spatie/laravel-activitylog v4.12.3 terinstall
- [x] barryvdh/laravel-dompdf v3.1.2 terinstall
- [x] alpinejs terinstall via npm
- [x] Tailwind CSS + Alpine.js dikonfigurasi di Vite
- [x] resources/css/app.css diperbaiki (sintaks @tailwind)
- [x] resources/js/app.js ditambahkan Alpine.js
- [x] docs/design/ folder tersedia
- [x] Git diinisialisasi, commit pertama: fase-0: persiapan

### Keputusan Fase 0
- Laravel versi: 12.69.2 (bukan 11 seperti di GEMINI.md). Fungsionalitas identik.
- Breeze stack: Blade, sesuai GEMINI.md.
- Alpine.js: Ditambahkan via npm, di-import di app.js.

---

## Fase 1 — Database, Seeder, Peran

### Tugas
- [x] 17 tabel database dimigrasi sesuai Bagian 6 GEMINI.md:
  `cabangs`, `users`, `karyawans`, `pelanggans`, `barangs`, `stok_barangs`, `mutasi_stoks`, `pembelians`, `pembelian_details`, `returs`, `sales_orders`, `surveys`, `fakturs`, `angsurans`, `surat_jalans`, `nomor_dokumens`, `settings`.
- [x] Model Eloquent lengkap beserta relasi, casts, dan soft deletes.
- [x] Trait `BelongsToCabang` dipasang di semua model bertabel `cabang_id` (kecuali master global `barangs` dan `cabangs`).
- [x] Cast `encrypted` pada `pelanggans.no_ktp`.
- [x] Helper global `setting($key, $default = null)` dan model `Setting` dengan method `get` & `set`.
- [x] Peran Spatie: `admin_pusat` dan `admin_cabang`.
- [x] Seeder lengkap (`DatabaseSeeder`):
  - User: Super Admin (`admin@nexa.com`, `admin_pusat`, cabang_id = null)
  - Cabang: Solo, Sragen, Tanon
  - User: Admin Solo (`solo@nexa.com`, `admin_cabang`), Admin Sragen (`sragen@nexa.com`, `admin_cabang`)
  - Karyawan tiap jabatan (sales, supervisor, kolektor, surveyor, driver)
  - Pelanggan contoh: Ahmad Riyadi (Solo), Bpk. Sudirman (Sragen), Ibu Siti Aminah (Sragen)
  - Barang contoh: Kulkas 2 Pintu Merk XYZ (Rp 4.000.000), TV LED, Mesin Cuci
  - Stok awal di setiap cabang
  - Setting default: `dp_hitung_angsuran_pertama` = `0`
- [x] Feature Test `Fase1Test`: 7 skenario pengujian isolasi cabang, login kedua peran, enkripsi KTP, setting helper, dan auto-assign cabang_id.

### Keputusan Fase 1
- `pelanggans.no_ktp` menggunakan tipe kolom `text` di migration agar kompatibel dengan panjang string terenkripsi Laravel `encrypted` cast.
- Helper global didaftarkan pada key `"files"` di `composer.json` (`app/Helpers/helpers.php`).
- Global scope `BelongsToCabang` mengecek role `admin_pusat`; selain role tersebut, query otomatis difilter hanya untuk `cabang_id` milik user yang sedang login.

### Cara Uji Fase 1
1. Jalankan `php artisan test --filter=Fase1Test` -> 7 passed (33 assertions).
2. Jalankan `php artisan test` -> 32 passed (94 assertions).
3. Jalankan `php artisan migrate:fresh --seed` -> database terisi data seeder.
4. Buka `http://nexaerp.test/login`:
   - Login `admin@nexa.com` / `password` (Super Admin - admin_pusat)
   - Login `solo@nexa.com` / `password` (Admin Cabang Solo - admin_cabang)

---

## Pertanyaan Terbuka (dari GEMINI.md bagian 14)

| # | Pertanyaan | Status |
|---|-----------|--------|
| 1 | Definisi Laba Bersih di dashboard & laporan | Belum dijawab |
| 2 | Ada denda keterlambatan & aturan penarikan barang? | Belum dijawab |
| 3 | Apakah pembayaran parsial diizinkan? | Belum dijawab |
| 4 | Arti pasti BPV dan Nota pada surat jalan | Belum dijawab |
| 5 | Kapan stok berkurang: saat faktur terbit atau saat terkirim? | Belum dijawab |
| 6 | Template kwitansi/nota klien (menunggu foto/scan) | Menunggu file |

---

## Catatan / Masalah

- 2026-09-29 (Fase 1): Migrasi kolom `cabang_id` dan `aktif` ke tabel `users` dibuat terpisah dan dijalankan setelah tabel `cabangs` terbuat untuk integritas foreign key.
- 2026-09-29 (Fase 1): Seluruh 32 test lulus.
