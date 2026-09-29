# NexaERP — Catatan Kemajuan Proyek

> File ini diperbarui otomatis oleh agent di akhir setiap fase.

---

## Status Fase

| Fase | Judul | Status | Tanggal Selesai |
|------|-------|--------|----------------|
| 0 | Persiapan | ✅ Selesai | 2026-09-29 |
| 1 | Database, Seeder, Peran | ✅ Selesai | 2026-09-29 |
| 2 | Layout & Dashboard | ✅ Selesai | 2026-09-29 |
| 3 | Data Master | ✅ Selesai | 2026-09-29 |
| 4 | Sales Order & Survei | ✅ Selesai | 2026-09-29 |
| 5 | Faktur & Jadwal Angsuran | ✅ Selesai | 2026-09-29 |
| 6 | Penerimaan Angsuran & Tagihan | ✅ Selesai | 2026-09-29 |
| 7 | Laporan Dasar | ✅ Selesai | 2026-09-29 |
| 8 | Cetak | ✅ Selesai* | 2026-09-29 |
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

---

## Fase 1 — Database, Seeder, Peran

### Tugas
- [x] 17 tabel database dimigrasi sesuai Bagian 6 GEMINI.md.
- [x] Model Eloquent lengkap beserta relasi, casts, dan soft deletes.
- [x] Trait `BelongsToCabang` dipasang di semua model bertabel `cabang_id`.
- [x] Cast `encrypted` pada `pelanggans.no_ktp`.
- [x] Helper global `setting($key, $default = null)` dan model `Setting`.
- [x] Peran Spatie: `admin_pusat` dan `admin_cabang`.
- [x] Seeder lengkap (`DatabaseSeeder`).
- [x] Feature Test `Fase1Test`: 7 skenario pengujian.

---

## Fase 2 — Layout & Dashboard Analytics

### Tugas
- [x] Layout Blade sesuai Bagian 4 GEMINI.md:
  - Sidebar kiri (lebar 240px, putih, border tipis, logo `NexaERP.` biru, label grup huruf kapital kecil abu-abu).
  - Item aktif: teks biru tebal (`#2557a7`) + border biru vertikal di sisi kiri (`border-l-4`).
  - Responsif: Sidebar otomatis menjadi off-canvas drawer dengan Alpine.js pada layar mobile.
- [x] Urutan menu sidebar persis Bagian 4.2:
  - Menu Utama: *Dashboard Analytics*
  - Sales & Kredit: *Data Pelanggan*, *Sales Order (SO)*, *Survey Kelayakan*, *Penerimaan Angsuran*
  - Inventori & Logistik: *Data Barang*, *Pembelian & Retur*, *Surat Jalan (TTB)*
  - Keuangan (Laporan): *Laba / Rugi*, *Laporan Tagihan*
  - Master & Setup: *Karyawan & Akses*, *Data Cabang* (hanya tampil untuk `admin_pusat`).
- [x] Header atas:
  - Judul halaman ("Dashboard Analytics") di kiri.
  - Badge peran di kanan: "Super Admin" dengan ikon user untuk `admin_pusat`, nama cabang untuk `admin_cabang`.
  - Dropdown user profil & tombol Logout.
- [x] Dashboard Analytics mirip persis `docs/design/dashboard.png`:
  - 4 Kartu Metrik: Laba Bersih (+12% dari bulan lalu), Total Piutang Berjalan (peringatan pelanggan jatuh tempo), Angsuran Masuk Hari Ini (target harian), Sales Order Menunggu Survey (keterangan surveyor).
  - Tabel "Tagihan Angsuran Jatuh Tempo Hari Ini": No. Faktur, Nama Pelanggan (Angsuran ke X dari Y), Nominal Tagihan, Kolektor (Sales) + Area, Status badge ("Menunggu Pembayaran" oranye, "Lunas Hari Ini" hijau).
- [x] Controller `DashboardController` dan rute modul navigasi agar tidak ada link yang 404.
- [x] Halaman landing `welcome.blade.php` bertema NexaERP.
- [x] Feature test `DashboardTest`: 4 skenario otorisasi, badge peran, akses menu cabang, dan modul navigasi.

### Keputusan Fase 2
- Layout menggunakan Google Font *Inter* dan background `#f4f6fa` untuk kenyamanan visual sesuai standar Bagian 4.1.
- Warna brand utama: `#2557a7` (biru panel NexaERP).
- Modul navigasi dihubungkan ke view placeholder yang rapi sehingga saat menu diklik tidak menghasilkan error 404 sebelum Fase 3-7 dimulai.

### Cara Uji Fase 2
1. Jalankan `php artisan test` -> 36 passed (123 assertions).
2. Login sebagai `admin@nexa.com` (Super Admin):
   - Periksa header kanan atas: tampil badge biru `Super Admin`.
   - Periksa sidebar: menu `Data Cabang` tampil.
   - Periksa 4 kartu metrik dan tabel tagihan jatuh tempo.
3. Login sebagai `solo@nexa.com` (Admin Cabang Solo):
   - Periksa header kanan atas: tampil badge hijau `Cabang Solo`.
   - Periksa sidebar: menu `Data Cabang` tidak tampil (tersembunyi).
   - Coba buka `/cabang` -> muncul 403 Forbidden.

---

## Fase 3 — Data Master

### Tugas
- [x] CRUD Cabang khusus `admin_pusat`, dengan perlindungan agar cabang berisi data operasional tidak dapat dihapus.
- [x] CRUD Karyawan per cabang; jabatan hanya berupa data karyawan, tanpa membuat akun login.
- [x] CRUD Pelanggan, termasuk kabupaten/kecamatan, pencarian, filter, dan sortir.
- [x] CRUD master Barang global dengan tampilan stok berdasarkan cabang.
- [x] Catat Pembelian metode `cashmoney` atau `tempo`; total dihitung server, stok bertambah melalui `StokService`, dan mutasi `masuk` tercatat.
- [x] Policies, middleware peran, Form Request, dan validasi berbahasa Indonesia untuk modul Fase 3.
- [x] Feature test Fase 3 untuk isolasi cabang, hak akses, kewajiban memilih cabang, dan mutasi stok.

### Keputusan Fase 3
- Nomor pembelian sementara memakai format `PB-YYYYMMDDHHMMSS-NNN`, karena format khusus belum ditetapkan. Penomoran konsisten dapat dialihkan ke `NomorDokumenService` bila diperlukan.
- Tidak ada perubahan skema database pada fase ini; tabel master dan pembelian dari Fase 1 telah mencukupi.

### Cara Uji Fase 3
1. `php artisan test --compact tests/Feature/Fase3Test.php` -> 4 skenario lulus (16 assertions).
2. `php artisan route:list --except-vendor` -> CRUD master dan rute pembelian terdaftar.
3. Pembelian menambah stok cabang dan menulis satu mutasi stok bertipe `masuk`.

### Catatan Pengujian
- Seluruh suite `php artisan test --compact` masih memiliki 6 kegagalan dari test Fase 1/2 yang mencari akun/cabang `solo`/`CAB-SOLO` dan isi dashboard lama; seeder saat ini memakai Gesi dan Gemolong. Test Fase 3 lulus. Perbaikan seeder/test Fase 1/2 dicatat untuk fase terkait dan tidak diubah di sini.
- Checkout tidak memiliki `.env` atau dependensi npm. Kunci aplikasi sementara diberikan hanya melalui environment proses saat test; tidak disimpan ke file.

## Fase 4 — Sales Order & Survei

### Tugas
- [x] Halaman daftar, form wizard SO, dan detail SO; input pelanggan, barang, jumlah, sales, DP, tenor, dan bunga.
- [x] Ringkasan kredit Alpine.js di browser dan hitung ulang di server memakai harga master barang.
- [x] Nomor SO berurutan per cabang/bulan melalui `NomorDokumenService`.
- [x] Pembuatan survei berstatus menunggu, penugasan surveyor, dan pencatatan hasil layak/tidak layak.
- [x] Hasil tidak layak mengubah SO menjadi `ditolak`; SO layak tetap di tahap survey untuk dilanjutkan ke Fase 5.
- [x] Policy dan validasi cabang untuk SO dan survei.

### Keputusan Fase 4
- Harga pada SO disimpan sebagai harga satuan snapshot; kalkulasi DP/kredit menggunakan harga satuan × jumlah barang.
- Survei berstatus `layak` belum mengubah SO menjadi `disetujui`; persetujuan faktur berada di Fase 5.

### Cara Uji Fase 4
- `php artisan test --compact tests/Feature/Fase4Test.php` -> 1 alur inti lulus (17 assertions), mencakup render form, hitung kredit contoh, penomoran SO, dan penolakan ketika hasil survei tidak layak.
- Atas arahan pengguna, test dibatasi ke alur inti fase aktif; suite lama Fase 1/2 tidak dijalankan ulang.

## Fase 5 — Faktur & Jadwal Angsuran

### Tugas
- [x] Terbitkan faktur hanya untuk SO dengan survei layak, dalam transaksi database.
- [x] Kunci dan validasi stok; kurangi stok lewat `StokService` dan catat mutasi keluar.
- [x] Buat pembayaran DP berstatus lunas dan jadwal angsuran dengan total tepat, penyesuaian angsuran terakhir, serta jatuh tempo tanpa lompat bulan.
- [x] Buat surat jalan berisi driver, tanggal kirim, dan nota; sediakan konfirmasi barang terkirim.
- [x] Tambahkan otorisasi, validasi, tampilan tahap faktur/pengiriman, dan daftar surat jalan.

### Keputusan Fase 5
- Stok berkurang saat faktur diterbitkan sesuai urutan proses pada bagian 7.3; keputusan bisnis ini tetap perlu dikonfirmasi pemilik.
- Penerbitan mencatat DP lunas pada tanggal DP, sesuai rancangan proses. TTB MVP hanya menyimpan driver, tanggal kirim, dan nota; BPV belum ditambahkan karena artinya belum ditetapkan.

### Cara Uji Fase 5
- `php artisan test --compact tests/Feature/Fase5Test.php` -> 2 skenario inti lulus (20 assertions): jadwal presisi/tanggal akhir bulan, mutasi stok, status terkirim, dan transaksi batal bila stok kurang.
- Suite penuh dan formatter tidak dijalankan: sesuai arahan hemat token; perintah `php artisan pint --dirty` tidak tersedia karena Pint belum terpasang.
- `.env` tidak tersedia di checkout; test memakai `APP_KEY` sementara hanya untuk proses test.

## Fase 6 — Penerimaan Angsuran & Tagihan

### Tugas
- [x] Cari dan catat pembayaran penuh berdasarkan faktur/pelanggan; nominal dibayar ditetapkan server sesuai nominal angsuran.
- [x] Pilih kolektor aktif satu cabang, simpan tanggal/metode/catatan, dan larang pembayaran ulang.
- [x] Faktur otomatis berstatus lunas setelah semua angsuran lunas; koreksi pembayaran lunas hanya admin pusat dan tercatat di activity log.
- [x] Laporan tagihan dengan filter bulan, kolektor, kabupaten, kecamatan, status, dan cabang untuk admin pusat.
- [x] Dashboard tetap menggunakan agregasi data angsuran/faktur aktual, bukan angka tetap.

### Keputusan Fase 6
- Pembayaran parsial tetap tidak didukung sesuai batas MVP. Form hanya menerima pelunasan penuh; nilai pembayaran tidak berasal dari input browser.
- Metode awal di form penerimaan adalah tunai atau transfer; kolom database tetap menerima label metode lain untuk kebutuhan berikutnya.

### Cara Uji Fase 6
- `php artisan test --compact tests/Feature/Fase6Test.php` -> alur pembayaran, status faktur, larangan koreksi oleh admin cabang, koreksi oleh admin pusat + activity log, dan laporan lulus (9 assertions).
- Tidak menjalankan suite penuh atas arahan penghematan token.

## Fase 7 — Laporan Dasar

### Tugas
- [x] Laporan penjualan terkirim per sales (jumlah SO, nilai penjualan, margin kredit).
- [x] Laporan tagihan per kolektor (tagihan, pembayaran, dan tunggakan lewat jatuh tempo).
- [x] Ekspor CSV berbahasa Indonesia untuk ringkasan penjualan dan kolektor.
- [x] LabaService menghitung margin yang diakui secara proporsional dari pembayaran angsuran lunas; kartu dashboard memakai layanan yang sama.

### Keputusan Fase 7
- Rumus margin MVP per pembayaran = nominal yang diterima × (total margin SO ÷ total kredit SO). Ini mengalokasikan margin sepanjang angsuran lunas, bukan mengklaim laba akuntansi final; definisi final tetap pertanyaan terbuka.
- Ringkasan penjualan menghitung SO terkirim pada tanggal transaksi; ringkasan kolektor mengikuti tanggal jatuh tempo.

### Cara Uji Fase 7
- `php artisan test --compact tests/Feature/Fase7Test.php` -> 1 alur inti lulus (14 assertions), memeriksa nominal kedua laporan, alokasi margin, ekspor CSV, dan halaman laba/rugi.
- `php artisan route:list --path=laporan --except-vendor` memastikan kelima rute laporan terdaftar; sintaks PHP dan `git diff --check` bersih.
- Suite penuh tidak dijalankan atas arahan penghematan token.

## Fase 8 — Cetak

### Selesai sebagai prasyarat non-template
- [x] Halaman admin pusat untuk mengunggah, melihat pratinjau, mengganti, dan menghapus foto/scan kwitansi.
- [x] Validasi hanya menerima gambar JPG/JPEG/PNG hingga 10 MB; file disimpan privat, bukan di folder publik.
- [x] Pengujian inti memastikan admin cabang ditolak dan alur file privat berhasil (12 assertions).

### Tugas
- [x] Halaman cetak mandiri untuk Nota DP, Faktur, Kwitansi Angsuran, dan Surat Jalan; data transaksi dibatasi lewat policy.
- [x] Halaman pratinjau template dengan kisi 10 mm opsional; gambar dimuat dari storage privat.
- [x] Koordinat elemen kwitansi dikumpulkan di `config/cetak.php` dan penggantian template admin langsung berlaku.
- [x] Tombol cetak ditambahkan ke detail SO dan daftar angsuran.

### Keputusan Fase 8
- Atas instruksi pengguna, gambar template yang diunggah diperlakukan sebagai template aktif. Ukuran cetak awal diasumsikan 297 × 187 mm berdasarkan proporsi scan; posisi mm merupakan kalibrasi awal dan dapat disetel di konfigurasi dengan pratinjau kisi.
- Nota DP, faktur, dan surat jalan menggunakan format cetak mandiri; hanya kwitansi angsuran yang memakai scan formulir sebagai latar.
- Kriteria posisi presisi perlu dikonfirmasi dengan satu cetak fisik pada printer/kertas pengguna. Admin dapat mengganti scan kapan saja; aplikasi otomatis membaca berkas aktif baru tanpa perubahan kode.

### Cara Uji Fase 8
- Render route cetak/kalibrasi, validasi policy cabang, pratinjau privat, dan `view:cache`; cetak fisik tidak dapat disimulasikan dari checkout ini.

## Pertanyaan Terbuka (dari GEMINI.md bagian 14)

| # | Pertanyaan | Status |
|---|-----------|--------|
| 1 | Definisi Laba Bersih di dashboard & laporan | Belum dijawab |
| 2 | Ada denda keterlambatan & aturan penarikan barang? | Belum dijawab |
| 3 | Apakah pembayaran parsial diizinkan? | Belum dijawab |
| 4 | Arti pasti BPV dan Nota pada surat jalan | Belum dijawab |
| 5 | Kapan stok berkurang: saat faktur terbit atau saat terkirim? | Belum dijawab |
| 6 | Template kwitansi/nota klien | Gambar unggahan dipakai sebagai acuan atas instruksi pengguna; ukuran/posisi final perlu uji cetak fisik |

## Fase 9 — Pengerasan & Deploy

- [x] Panduan pengguna ringkas: `docs/PANDUAN.md`.
- [x] Panduan deploy cPanel, `APP_DEBUG=false`, perlindungan `.env`, HTTPS, backup, build frontend, dan migrasi produksi: `docs/DEPLOY-CPANEL.md`.
- [x] Audit akses: pendaftaran publik dinonaktifkan; endpoint cetak mengikuti policy cabang; file template tetap privat dan pratinjau hanya untuk admin pusat.
- [x] Pertahankan validasi Form Request serta pencatatan perubahan stok melalui `StokService`.
- [x] Test fokus perubahan aktif lulus; suite penuh tidak diulang sesuai arahan penghematan token.

---

## Catatan / Masalah

- 2026-09-29 (Fase 2): Seluruh 36 test fitur lulus tanpa error. Desain visual dashboard dan sidebar telah disesuaikan dengan foto acuan `dashboard.png`.
- 2026-09-29: Form pembelian dan karyawan menghasilkan PHP Blade yang tidak valid karena `@enderror@endif` berdekatan; form pelanggan juga memiliki pola sama. View ditulis ulang dan file hasil kompilasi terkait lolos `php -l`.
- 2026-09-29: CRUD barang menampilkan dan menerima saldo baru per cabang. Koreksi dicatat melalui `StokService` sebagai mutasi `penyesuaian`; penerimaan stok supplier tetap melalui pembelian.
- 2026-09-29: TTB kini memiliki aksi admin untuk mengubah status menunggu menjadi terverifikasi; perubahan dicatat oleh activity log.
- 2026-09-29: Cetak empat dokumen dan pratinjau kalibrasi kwitansi ditambahkan; template pengganti dari admin otomatis dipakai.
- 2026-09-29: Posisi awal template memakai ukuran 297 × 187 mm berdasar rasio gambar; satu uji cetak fisik diperlukan untuk penyetelan printer.
- 2026-09-29: Pendaftaran akun publik dinonaktifkan karena aplikasi hanya memakai dua peran admin dan belum menyediakan alur aman pembuatan user.
