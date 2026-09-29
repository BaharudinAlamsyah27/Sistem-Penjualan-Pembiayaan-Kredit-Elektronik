# NexaERP — Catatan Kemajuan Proyek

> File ini diperbarui otomatis oleh agent di akhir setiap fase.

---

## Status Fase

| Fase | Judul | Status | Tanggal Selesai |
|------|-------|--------|----------------|
| 0 | Persiapan | ✅ Selesai | 2026-09-29 |
| 1 | Database, Seeder, Peran | ⏳ Belum dimulai | — |
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

## Fase 1 — Database, Seeder, Peran (Belum dimulai)

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

- 2026-09-29 (Fase 0): BelongsToCabang.php sudah ada sebelum Fase 0, dipertahankan.
- 2026-09-29 (Fase 0): Beberapa model awal sudah ada tapi belum lengkap, dilengkapi di Fase 1.
- 2026-09-29 (Fase 0): Migration sebagian sudah ada, di-review di Fase 1.
- 2026-09-29 (Fase 0): app.css memakai sintaks @tailwindcss (salah), diperbaiki ke @tailwind.
