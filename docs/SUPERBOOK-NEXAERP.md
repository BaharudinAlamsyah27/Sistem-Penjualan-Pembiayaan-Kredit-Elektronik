# SUPERBOOK NexaERP

**Panduan pengguna, programmer, pemeliharaan, dan keselamatan perubahan**  
Versi dokumentasi: 1.0 · Diperiksa terhadap struktur proyek: 29 September 2026

> Dokumen ini menjelaskan aplikasi yang ada di repositori. Sebagian fitur tahap berikutnya, penyesuaian printer, dan instalasi produksi dapat bergantung pada data, perangkat, atau hosting pemilik.

## Daftar isi

1. Ringkasan dan istilah
2. Panduan pemakaian untuk pengguna
3. Peta sistem dan alur data
4. Kamus folder dan berkas programmer
5. Cara mengubah fitur dengan aman
6. Perawatan rutin dan pemulihan masalah
7. Rilis dan deploy
8. Batas fitur dan hal yang harus dikonfirmasi

---

## 1. Ringkasan dan istilah

NexaERP adalah aplikasi web Laravel untuk penjualan elektronik secara kredit pada beberapa cabang. Database dipakai bersama; data operasional dibatasi menurut cabang. Peran yang dipakai adalah **admin_pusat** dan **admin_cabang**. Sales, kolektor, surveyor, dan driver adalah data karyawan, bukan akun login.

### Istilah sederhana

| Istilah | Arti di NexaERP |
|---|---|
| Dashboard | Ringkasan aktivitas dan angka operasional. |
| Cabang | Unit usaha. Admin cabang hanya boleh mengakses cabangnya. |
| Master | Data dasar yang dipakai berulang, misalnya barang, pelanggan, dan karyawan. |
| Sales Order (SO) | Permintaan penjualan sebelum survei dan penerbitan faktur. |
| Survei | Pemeriksaan pelanggan. Hasil `layak` memungkinkan transaksi dilanjutkan. |
| DP | Uang muka. Dicatat terpisah sebagai pembayaran DP. |
| Tenor | Banyaknya bulan angsuran. |
| Margin flat | Margin dihitung dari pokok × persen bulanan × tenor. Ini bukan bunga majemuk. |
| Faktur | Tagihan transaksi yang memiliki snapshot harga/kredit dan jadwal angsuran. |
| Angsuran | Baris jadwal pembayaran untuk suatu faktur. Pembayaran parsial belum didukung. |
| Surat jalan / TTB | Dokumen pengiriman barang. Dapat berstatus menunggu atau terverifikasi. |
| Stok cabang | Saldo barang di cabang tertentu, terpisah dari master barang. |
| Mutasi stok | Catatan setiap barang masuk, keluar, atau saldo yang disesuaikan. |
| Policy | Aturan yang memutuskan apakah user boleh melihat atau mengubah record. |
| Form Request | Validasi dan otorisasi input sebelum controller memprosesnya. |
| Migration | Riwayat perubahan struktur tabel yang dapat dijalankan berurutan. |
| Seeder | Data awal/demo untuk instalasi atau pengujian. Jangan seed demo ke database produksi tanpa rencana. |
| Blade | Mesin template Laravel untuk membangun halaman HTML. |
| Eloquent | ORM Laravel: cara membaca/menyimpan tabel melalui model PHP. |
| Service | Kelas untuk logika bisnis yang dipakai controller, misalnya hitung kredit atau mutasi stok. |
| Soft delete | Record diberi penanda terhapus, bukan langsung dihapus permanen. |
| Transaction | Kelompok operasi database yang berhasil seluruhnya atau dibatalkan seluruhnya. |
| Lock for update | Mengunci baris saat transaksi untuk mencegah dua proses mengubah stok/nomor secara bersamaan. |

### Teknologi yang terpasang

- Backend: PHP `^8.2`, Laravel `^12.0` (versi paket nyata; bukan Laravel 11 seperti rencana awal).
- Tampilan: Blade, Tailwind CSS, Alpine.js, dan Vite.
- Database utama lokal: MySQL/Laragon; test menggunakan konfigurasi PHPUnit yang terpisah.
- Peran: `spatie/laravel-permission`; log perubahan: `spatie/laravel-activitylog`.
- Cetak PDF server tersedia sebagai dependency DomPDF, sementara alur cetak saat ini memakai halaman HTML dan dialog cetak browser.

---

## 2. Panduan pemakaian untuk pengguna

### 2.1 Masuk dan memahami hak akses

1. Buka alamat aplikasi yang diberikan administrator, lalu masuk dengan email dan kata sandi akun aktif Anda.
2. Admin pusat dapat memilih/meninjau lintas cabang dan mengelola cabang serta template kwitansi.
3. Admin cabang bekerja dalam batas cabangnya. Data cabang lain seharusnya tidak terlihat atau dapat dibuka melalui tautan langsung.
4. Akun publik baru tidak dapat didaftarkan dari halaman web. Pembuatan akun admin perlu dilakukan oleh pengelola teknis dengan otorisasi pemilik.
5. Ubah kata sandi dari menu profil dan jangan berbagi akun.

**Catatan lokal:** seeder menyediakan akun demo untuk pengembangan. Kredensial demo hanya boleh dipakai di lingkungan lokal, tidak di hosting produksi. Jangan menganggap data demo sebagai data usaha.

### 2.2 Siapkan data sebelum transaksi

1. Admin pusat memastikan cabang aktif dan admin cabang sudah diprovisikan.
2. Masukkan karyawan dengan jabatan yang benar: sales, surveyor, kolektor, driver, atau supervisor. Data karyawan tidak membuat akun login.
3. Masukkan pelanggan termasuk alamat, kabupaten, kecamatan, dan nomor kontak. Nomor KTP disimpan terenkripsi oleh aplikasi.
4. Tambahkan master barang beserta harga beli dan jual.
5. Masukkan stok awal secara hati-hati. Penyesuaian stok adalah koreksi saldo fisik, bukan cara mencatat penerimaan supplier.

### 2.3 Menambah stok lewat pembelian

1. Buka **Pembelian & Retur** lalu pilih catat pembelian.
2. Isi tanggal, supplier, metode `cashmoney` atau `tempo`, barang, kuantitas, dan harga beli.
3. Periksa total yang dihitung server lalu simpan.
4. Pembelian menambah stok cabang melalui StokService dan membuat mutasi `masuk`.
5. Jika stok lama salah, edit saldo dari form barang hanya setelah menghitung stok fisik; perubahan akan dicatat sebagai `penyesuaian`.

Jangan mengubah tabel stok langsung melalui phpMyAdmin untuk membetulkan saldo. Cara itu melewati mutasi audit dan berisiko membuat catatan stok berbeda.

### 2.4 Dari SO sampai pengiriman

1. Buka **Sales Order (SO)** dan buat transaksi untuk pelanggan, barang, jumlah, sales, DP, tenor, dan bunga.
2. Angka ringkasan di browser adalah pratinjau. Server menghitung ulang sebelum menyimpan.
3. Tugaskan surveyor dan catat hasil survei. Hasil tidak layak menolak SO; hanya hasil layak yang dapat diteruskan.
4. Dari detail SO yang layak, terbitkan faktur. Isi tanggal DP, driver, tanggal kirim, dan catatan nota bila ada.
5. Sistem memeriksa stok cabang dan mengurangi stok ketika faktur diterbitkan. Jika stok kurang, proses harus gagal tanpa menerbitkan faktur sebagian.
6. Sistem mencatat DP dan membuat jadwal angsuran. Jatuh tempo mengikuti tanggal DP; pembulatan selisih diletakkan pada angsuran terakhir.
7. Gunakan tombol **Konfirmasi Barang Terkirim** setelah pengiriman selesai. Status SO menjadi terkirim.
8. Pada daftar Surat Jalan, admin dapat menandai dokumen terverifikasi.

### 2.5 Mencatat pembayaran

1. Buka **Penerimaan Angsuran**, cari nomor faktur atau nama pelanggan.
2. Catat tanggal bayar, metode, dan kolektor bila diketahui.
3. Aplikasi mencatat pembayaran penuh sesuai nilai jadwal. Nilai nominal tidak bisa dikurangi dari browser; pembayaran parsial tidak tersedia.
4. Setelah semua angsuran lunas, status faktur berubah lunas.
5. Pembayaran yang sudah lunas hanya dapat dikoreksi admin pusat dan perubahan tercatat.
6. Cetak kwitansi melalui tautan **Cetak kwitansi** pada baris pembayaran lunas.

### 2.6 Laporan

- **Laporan Penjualan:** rekap SO terkirim per sales dan nilai penjualan/margin.
- **Laporan Kolektor:** tagihan, pembayaran, dan tunggakan per kolektor.
- **Laporan Tagihan:** filter periode, kolektor, wilayah, status, dan cabang untuk admin pusat.
- **Laba/Rugi:** angka margin versi MVP, dialokasikan proporsional terhadap pembayaran angsuran yang lunas. Angka ini belum definisi laba akuntansi final.
- Ekspor yang tersedia berupa CSV.

### 2.7 Cetak dan ganti template kwitansi

1. Admin pusat buka **Template Kwitansi**.
2. Unggah JPG/JPEG/PNG maksimal 10 MB. Template disimpan di storage privat, bukan URL publik.
3. Gunakan **Pratinjau posisi** atau **Tampilkan garis bantu 10 mm**.
4. Kwitansi angsuran mengambil template yang sedang aktif. Jika admin menggantinya, halaman kwitansi berikutnya otomatis membaca file baru; programmer tidak perlu mengedit kode.
5. Ukuran awal template cetak adalah sekitar 297 × 187 mm. Di dialog cetak pilih skala 100%/actual size, matikan header/footer browser, dan pastikan margin printer nol/tanpa margin jika perangkat mendukung.
6. Cetak satu lembar uji. Jika bergeser, programmer mengubah koordinat di `config/cetak.php`, bukan mengacak CSS banyak halaman.
7. Nota DP, faktur, dan surat jalan saat ini adalah layout cetak mandiri, bukan form scan kwitansi.

### 2.8 Jika ada masalah

| Gejala | Yang bisa diperiksa pengguna |
|---|---|
| Menu/halaman tidak terbuka | Keluar-masuk kembali, pastikan akun aktif, catat URL dan pesan error. |
| Pelanggan/barang tidak muncul | Pastikan cabang terpilih benar, data aktif, dan admin cabang berada pada cabang yang sama. |
| Faktur tidak bisa diterbitkan | Periksa hasil survei harus layak dan stok cukup. Jangan membuat faktur duplikat. |
| Kwitansi tidak bisa dicetak | Pastikan pembayaran berstatus lunas dan browser mengizinkan tab/dialog cetak. |
| Template baru belum terlihat | Refresh halaman cetak; pastikan upload berhasil dan pratinjau admin menampilkan gambar baru. |
| Salah input uang/pembayaran | Jangan menghapus record/database. Hubungi admin pusat agar koreksi tercatat. |

Saat melapor, sertakan jam kejadian, cabang, menu, nomor SO/faktur, langkah terakhir, dan screenshot. Jangan mengirim kata sandi, APP_KEY, nomor KTP, atau isi `.env`.

---

## 3. Peta sistem dan alur data

### 3.1 Alur permintaan web

```text
Browser
  -> routes/web.php (URL dan middleware auth/verified/role)
  -> Controller (mengatur alur request)
  -> Policy + Form Request (izin dan validasi)
  -> Service (aturan bisnis yang harus konsisten)
  -> Model Eloquent -> MySQL
  -> Blade view -> Browser
```

Jangan letakkan seluruh aturan bisnis dalam Blade atau controller. Jangan mempercayai angka dari browser untuk harga, DP, stok, total, atau pembayaran.

### 3.2 Jalur transaksi ringkas

```text
Master pelanggan/barang/karyawan
  -> SO + survei
  -> Faktur + DP + jadwal + surat jalan + stok keluar
  -> pengiriman / verifikasi TTB
  -> pembayaran angsuran + activity log
  -> laporan / cetak
```

### 3.3 Batas cabang dan data uang

- Model operasional yang memiliki `cabang_id` menggunakan trait `BelongsToCabang` untuk membatasi query admin cabang.
- Hak melihat/mengubah juga diperiksa oleh Policy dan middleware. Menyembunyikan menu saja bukan pengamanan.
- Master `barangs` global; tabel `stok_barangs` memegang jumlah per cabang.
- Nominal kredit disimpan sebagai snapshot di SO; perubahan harga barang berikutnya tidak mengubah kredit historis.
- Nominal uang disimpan desimal di database. Format tampilan Indonesia: `Rp 4.000.000`.
- Stok mutasi selalu dicatat lewat `StokService`. Penerbitan faktur/pembelian menggunakan transaksi DB dan penguncian baris.

---

## 4. Kamus folder dan berkas programmer

### 4.1 Folder tingkat atas

| Folder/berkas | Fungsi | Perhatian |
|---|---|---|
| `app/` | Kode aplikasi: controller, request, model, policy, service. | Tempat utama logika aplikasi. Ikuti pola yang sudah ada. |
| `bootstrap/` | Bootstrap Laravel dan cache framework. | Jangan edit file cache; gunakan perintah Artisan. |
| `config/` | Konfigurasi aplikasi/paket; termasuk koordinat cetak. | Nilai rahasia dibaca dari environment, jangan ditanam di sini. |
| `database/migrations/` | Evolusi skema tabel. | Migration baru harus aman untuk data lama dan punya `down()`. |
| `database/seeders/` | Data demo/awal. | Jangan jalankan seeder demo tanpa sengaja pada data produksi. |
| `database/factories/` | Pembuatan record contoh untuk test. | Jaga relasi/field factory tetap sesuai skema. |
| `docs/` | Panduan, kemajuan fase, desain acuan. | Perbarui bila keputusan bisnis atau prosedur berubah. |
| `public/` | Document root web, aset hasil build. | Hanya berkas yang memang boleh publik; jangan taruh `.env`/template privat. |
| `resources/css`, `resources/js` | Sumber aset frontend. | Perubahan perlu `npm run build` untuk produksi. |
| `resources/views/` | Halaman Blade dan komponen antarmuka. | Hindari logika bisnis; validasi kesalahan Blade dengan `view:cache`. |
| `routes/` | Definisi URL dan middleware. | Setelah perubahan, bersihkan/build ulang cache route. |
| `storage/app/private/` | File privat, termasuk template cetak aktif. | Backup bersama database; jangan pindahkan ke `public`. |
| `storage/framework/` | Cache view/session/framework. | Isi dapat dibuat ulang; jangan commit. |
| `storage/logs/` | Log aplikasi. | Periksa sebelum menghapus; log dapat mengandung data pribadi. Batasi akses. |
| `tests/` | Test otomatis. | Tambahkan test fokus pada aturan penting yang berubah. |
| `vendor/`, `node_modules/` | Dependency hasil install. | Jangan edit/commit manual; kelola lewat Composer/npm. |
| `.env` | Rahasia dan konfigurasi lokal/produksi. | Jangan pernah commit, screenshot, kirim, atau tempel ke chat. |
| `.env.example` | Template nama konfigurasi tanpa rahasia. | Aman dibagikan; jangan taruh password/APP_KEY asli. |
| `artisan` | CLI aplikasi Laravel. | Jalankan dari akar proyek. |
| `composer.json` / `.lock` | Dependency PHP dan versi terkunci. | Ubah dengan Composer; pertimbangkan kompatibilitas cPanel. |
| `package.json` / `package-lock.json` | Dependency frontend terkunci. | Gunakan `npm ci` untuk instalasi reproduktif. |
| `vite.config.js`, `tailwind.config.js`, `postcss.config.js` | Pipeline aset CSS/JS. | Jangan edit untuk masalah data/database. |
| `phpunit.xml` | Konfigurasi test. | Pastikan test tidak terhubung ke database produksi. |
| `AGENTS.md`, `GEMINI.md` | Arahan dan keputusan proyek. | Baca bagian terkait sebelum mengubah domain bisnis. |

### 4.2 `app/Http/Controllers/` — endpoint dan alur

| Berkas | Tanggung jawab |
|---|---|
| `Controller.php` | Basis controller dan otorisasi Laravel. |
| `DashboardController.php` | Menyiapkan kartu dashboard dan data jatuh tempo. |
| `CabangController.php` | CRUD cabang admin pusat. |
| `KaryawanController.php` | CRUD data karyawan per cabang. |
| `PelangganController.php` | CRUD, pencarian, filter pelanggan. |
| `BarangController.php` | CRUD barang, tampilan stok cabang, delegasi saldo ke StokService. |
| `PembelianController.php` | Form/daftar/pencatatan pembelian stok masuk. |
| `SalesOrderController.php` | Daftar, form, penyimpanan, detail SO dan ringkasan kredit. |
| `SurveyController.php` | Penugasan surveyor dan penyimpanan hasil survei. |
| `PenerbitanFakturController.php` | Penerbitan faktur, DP, angsuran, surat jalan, pengurangan stok; konfirmasi terkirim. |
| `PenerimaanAngsuranController.php` | Cari tagihan, catat pembayaran penuh, koreksi oleh admin pusat. |
| `SuratJalanController.php` | Daftar dan aksi verifikasi TTB. |
| `LaporanTagihanController.php` | Laporan tagihan dan filter periode/wilayah/cabang. |
| `LaporanController.php` | Penjualan, kolektor, laba/rugi MVP, ekspor CSV. |
| `CetakController.php` | Halaman cetak invoice/DP/angsuran/TTB dan pratinjau template. |
| `TemplateKwitansiController.php` | Upload, lihat, ganti, hapus file template privat. |
| `ProfileController.php` | Profil, kata sandi, dan penghapusan akun milik sendiri. |
| `Auth/AuthenticatedSessionController.php` | Login/logout. |
| `Auth/ConfirmablePasswordController.php` | Konfirmasi kata sandi untuk aksi sensitif. |
| `Auth/EmailVerificationNotificationController.php` | Pengiriman ulang verifikasi email. |
| `Auth/EmailVerificationPromptController.php` | Halaman permintaan verifikasi email. |
| `Auth/NewPasswordController.php` | Form dan penyimpanan reset password. |
| `Auth/PasswordController.php` | Perubahan password setelah login. |
| `Auth/PasswordResetLinkController.php` | Meminta tautan reset password. |
| `Auth/VerifyEmailController.php` | Validasi tautan verifikasi. |

### 4.3 `app/Http/Requests/` — input tervalidasi

`Auth/LoginRequest.php` menangani validasi login dan rate limit lima percobaan. `BarangRequest.php` memvalidasi master barang/stok; `CabangRequest.php` cabang; `KaryawanRequest.php` karyawan; `PelangganRequest.php` pelanggan; `PembelianRequest.php` pembelian dan detail; `SalesOrderRequest.php` data SO dan batas kredit; `SurveyRequest.php` hasil survei; `PenerbitanFakturRequest.php` tanggal DP, driver, jadwal kirim; `PenerimaanAngsuranRequest.php` pembayaran dan kolektor; `TemplateKwitansiRequest.php` tipe/ukuran upload; `ProfileUpdateRequest.php` profil user.

**Aturan:** controller memakai `$request->validated()`, bukan `$request->all()`. Pesan validasi antarmuka berbahasa Indonesia. Validasi client-side hanya untuk kemudahan; server tetap otoritatif.

### 4.4 `app/Models/` — representasi tabel

| Berkas | Tabel/konsep |
|---|---|
| `User.php` | Akun login, role, cabang, status aktif. |
| `Cabang.php` | Cabang usaha. |
| `Karyawan.php` | Data sales/surveyor/kolektor/driver/supervisor. |
| `Pelanggan.php` | Data pelanggan; `no_ktp` memakai cast terenkripsi. |
| `Barang.php` | Master barang global. |
| `StokBarang.php` | Saldo barang per cabang. |
| `MutasiStok.php` | Jejak barang masuk/keluar/penyesuaian. |
| `Pembelian.php` | Header pembelian. |
| `PembelianDetail.php` | Baris barang pembelian. |
| `Retur.php` | Model transaksi retur yang disiapkan. Cakupan retur lengkap belum menjadi alur operasional MVP. |
| `SalesOrder.php` | Header SO dan snapshot nilai kredit; relasi pelanggan/barang/sales/survei/faktur/TTB. |
| `Survey.php` | Hasil/penugasan survei. |
| `Faktur.php` | Tagihan transaksi dan statusnya. |
| `Angsuran.php` | Detail DP dan jadwal angsuran. |
| `SuratJalan.php` | TTB, driver, tanggal kirim, nota, verifikasi. |
| `NomorDokumen.php` | Counter nomor berurutan per cabang/periode/jenis. |
| `Setting.php` | Key-value pengaturan, termasuk lokasi template aktif. |
| `Concerns/BelongsToCabang.php` | Trait global scope cabang dan pengisian cabang pada pembuatan model. |

### 4.5 `app/Services/`, policy, dan bootstrap

| Berkas | Fungsi / aturan |
|---|---|
| `Services/KreditService.php` | Hitung margin flat dan generate jadwal; selisih pembulatan masuk angsuran terakhir. |
| `Services/NomorDokumenService.php` | Nomor dokumen per cabang/periode; gunakan transaksi/lock. |
| `Services/StokService.php` | Saldo masuk/keluar/koreksi dan mutasi stok dalam DB. Jangan update stok langsung di controller. |
| `Services/LabaService.php` | Margin MVP berdasar porsi pembayaran; bukan laporan akuntansi final. |
| `Policies/AngsuranPolicy.php` | Melihat/mencatat angsuran; koreksi hanya admin pusat. |
| `Policies/BarangPolicy.php` | Hak melihat/mengubah master barang dan stok cabang. |
| `Policies/CabangPolicy.php` | Membatasi pengelolaan cabang ke admin pusat. |
| `Policies/FakturPolicy.php` | Akses faktur sesuai peran dan cabang. |
| `Policies/KaryawanPolicy.php` | Membatasi record karyawan sesuai cabang. |
| `Policies/PelangganPolicy.php` | Membatasi record pelanggan sesuai cabang. |
| `Policies/PembelianPolicy.php` | Membatasi pembelian sesuai cabang. |
| `Policies/SalesOrderPolicy.php` | Membatasi akses SO dan aksi yang diperbolehkan. |
| `Policies/SurveyPolicy.php` | Membatasi akses survei berdasarkan cabang. |
| `Policies/SuratJalanPolicy.php` | Hak lihat/verifikasi TTB sesuai cabang. |
| `Providers/AppServiceProvider.php` | Registrasi/boot layanan aplikasi. |
| `Helpers/helpers.php` | Helper global `setting(key, default)`; terdaftar lewat autoload Composer. |
| `View/Components/AppLayout.php` | Komponen layout aplikasi terautentikasi. |
| `View/Components/GuestLayout.php` | Layout login/guest. |

### 4.6 Route, tampilan, konfigurasi, dan database

- `routes/web.php`: halaman dashboard, master, SO/survei, pembayaran, laporan, pengaturan template, dan cetak. Mayoritas berada di middleware `auth` + `verified`; cabang memakai role pusat. Sebelum menambah route periksa middleware **dan** Policy.
- `routes/auth.php`: login, reset, verifikasi, profil auth; route register sengaja tidak dipasang.
- `routes/console.php`: tempat command Artisan buatan proyek bila kelak dibutuhkan.
- `resources/views/layouts/app.blade.php`, `sidebar.blade.php`, `navigation.blade.php`: kerangka halaman, menu desktop/mobile.
- `resources/views/dashboard.blade.php`: dashboard; `master/{barang,cabang,karyawan,pelanggan,pembelian}/`: halaman daftar/form master.
- `resources/views/sales-order/`: daftar, input, detail, dan form penerbitan faktur; `survey/`, `angsuran/`, `surat-jalan/`, `laporan/`: modul terkait nama folder.
- `resources/views/cetak/`: view mandiri nota DP, faktur, kwitansi, surat jalan, kalibrasi. `components/cetak-layout.blade.php` adalah pembungkus cetak aktif.
- `resources/views/auth/` dan `profile/`: halaman login/reset/verifikasi serta profil. Pendaftaran publik tidak diaktifkan dan scaffold register-nya sudah dibersihkan.
- `resources/views/components/`: potongan UI Blade bersama seperti tombol, input, dropdown, logo, layout cetak.
- `resources/css/app.css`, `resources/js/app.js`, `resources/js/bootstrap.js`: sumber CSS/JS; Tailwind/Alpine masuk lewat Vite.
- `config/cetak.php`: ukuran kertas/koordinat elemen. `config/app.php`, `auth.php`, `database.php`, `filesystems.php`, `cache.php`, `session.php`, `queue.php`, `mail.php`, `logging.php`, `permission.php`, `services.php`: konfigurasi framework/paket; secret berasal `.env`.
- Migration `0001_01_01_*`: users, cache, jobs. Migration `2026_09_29_022040*`: cabang; `022041*`: cabang/status user; `022042*`: barang/stok, karyawan, pelanggan, pembelian/retur, settings; `022043*`: SO dan survei; `022044*`: faktur/angsuran; `025955*`: tabel Spatie role; `025957*` s.d. `025959*`: activity log.
- `DatabaseSeeder.php`: akun/cabang/master demo dan setting; `DashboardDemoSeeder.php`: dataset dashboard demo. `UserFactory.php`: user test.
- `docs/design/`: gambar acuan dashboard dan SO. `docs/PROGRESS.md`: status fase/keputusan terbuka; `PANDUAN.md` dan `DEPLOY-CPANEL.md`: petunjuk ringkas terkait.
- `tests/Feature/`: auth, fase 1/3-7, dashboard, template dan perbaikan pengguna. `tests/Unit/ExampleTest.php`: contoh unit test. `tests/TestCase.php`: bootstrap test Laravel.

### 4.7 Indeks file tampilan, migration, dan test

**Tampilan layout dan bersama:** `layouts/app.blade.php` (kerangka login), `layouts/guest.blade.php` (kerangka guest), `layouts/navigation.blade.php` (navigasi Breeze), `layouts/sidebar.blade.php` (menu kiri), `components/application-logo.blade.php` (logo), `components/auth-session-status.blade.php` (pesan auth), `components/cetak-layout.blade.php` (kertas cetak), `components/danger-button.blade.php` (aksi berbahaya), `components/dropdown-link.blade.php` (tautan menu), `components/dropdown.blade.php` (wadah menu), `components/input-error.blade.php` (error form), `components/input-label.blade.php` (label), `components/modal.blade.php` (modal), `components/nav-link.blade.php` (tautan nav), `components/primary-button.blade.php` (tombol utama), `components/responsive-nav-link.blade.php` (nav mobile), `components/secondary-button.blade.php` (tombol sekunder), `components/text-input.blade.php` (input teks).

**Tampilan per modul:** `dashboard.blade.php` ringkasan; `master/barang/index.blade.php` daftar barang/stok dan `master/barang/form.blade.php` form barang; pasangan `index/form` pada `master/cabang/`, `master/karyawan/`, `master/pelanggan/`, dan `master/pembelian/` masing-masing untuk daftar dan input modulnya; `sales-order/index.blade.php` daftar SO, `create.blade.php` wizard input, `show.blade.php` detail/alur, `faktur.blade.php` form penerbitan; `survey/index.blade.php` antrean, `survey/edit.blade.php` penugasan/hasil; `angsuran/index.blade.php` penerimaan; `surat-jalan/index.blade.php` pengiriman dan verifikasi; `laporan/kolektor.blade.php`, `laba-rugi.blade.php`, `penjualan.blade.php`, `tagihan.blade.php` masing-masing laporan terkait; `pengaturan/template-kwitansi.blade.php` upload/pratinjau admin.

**Cetak/auth/profil:** `cetak/faktur.blade.php`, `kalibrasi.blade.php`, `kwitansi.blade.php`, `nota-dp.blade.php`, `surat-jalan.blade.php`; `welcome.blade.php` landing; `auth/login.blade.php`, `confirm-password.blade.php`, `forgot-password.blade.php`, `reset-password.blade.php`, `verify-email.blade.php`; `profile/edit.blade.php` halaman profil serta `profile/partials/delete-user-form.blade.php`, `update-password-form.blade.php`, `update-profile-information-form.blade.php` bagian form profil.

**Migration bernama (timestamp menjelaskan urutan):** `0001_01_01_000000_create_users_table.php` akun; `0001_01_01_000001_create_cache_table.php` cache; `0001_01_01_000002_create_jobs_table.php` antrean; `2026_09_29_022040_create_cabangs_table.php`; `2026_09_29_022041_add_cabang_id_and_aktif_to_users_table.php`; `2026_09_29_022042_create_barangs_and_stoks_table.php`; `2026_09_29_022042_create_karyawans_table.php`; `2026_09_29_022042_create_pelanggans_table.php`; `2026_09_29_022042_create_pembelians_and_returs_table.php`; `2026_09_29_022042_create_settings_table.php`; `2026_09_29_022043_create_sales_orders_table.php`; `2026_09_29_022043_create_surveys_table.php`; `2026_09_29_022044_create_fakturs_and_angsurans_table.php`; `2026_09_29_025955_create_permission_tables.php`; `2026_09_29_025957_create_activity_log_table.php`; `2026_09_29_025958_add_event_column_to_activity_log_table.php`; `2026_09_29_025959_add_batch_uuid_column_to_activity_log_table.php`.

**Test yang tersedia:** `Feature/Auth/AuthenticationTest.php` login; `EmailVerificationTest.php` verifikasi; `PasswordConfirmationTest.php` konfirmasi; `PasswordResetTest.php` reset; `PasswordUpdateTest.php` update; `RegistrationTest.php` memastikan register publik tertutup; `DashboardTest.php` dashboard/akses; `Fase1Test.php` database-role-scope; `Fase3Test.php` master; `Fase4Test.php` SO-survei-kredit; `Fase5Test.php` faktur-jadwal-stok; `Fase6Test.php` bayar-tagihan; `Fase7Test.php` laporan; `PerbaikanPenggunaTest.php` regresi form/stok/verifikasi TTB; `TemplateKwitansiTest.php` upload privat/pratinjau; `ProfileTest.php` profil; `ExampleTest.php` contoh feature; `Unit/ExampleTest.php` contoh unit. Test adalah alat verifikasi, bukan tempat menyimpan data operasional.

---

## 5. Cara mengubah fitur dengan aman

### Urutan kerja yang disarankan

1. Baca `AGENTS.md`, bagian keputusan bisnis, keamanan, fase terkait, serta `docs/PROGRESS.md`.
2. Pastikan perubahan berada pada satu tujuan/per fase. Tuliskan file yang mungkin berubah sebelum mulai.
3. Telusuri route → controller → Form Request → policy → service/model → view → test. Cari pemakaian route/model sebelum mengubah nama atau kolom.
4. Jangan menebak aturan bisnis terbuka seperti denda, retur penuh, pembayaran parsial, atau definisi laba final. Tandai pertanyaan dan ambil keputusan pemilik.
5. Buat perubahan sekecil mungkin; pertahankan Bahasa Indonesia pada UI dan validasi.
6. Jalankan lint/compile view dan test fokus pada aturan yang disentuh. Hindari migrasi destruktif di database aktif.
7. Periksa `git diff --check`, `git status`, file rahasia, dan perubahan tidak terkait; perbarui dokumentasi.
8. Simpan commit bermakna sesuai aturan proyek. Jangan campur `.codex/`, `.env`, cache, backup, atau output sementara.

### Aturan keselamatan wajib

- **Jangan jalankan `php artisan migrate:fresh --seed` terhadap Laragon/produksi yang berisi data penting.** Perintah ini menghapus seluruh tabel. Pakai database test/baru saja setelah memeriksa koneksi.
- Buat migration baru untuk perubahan skema. Jangan edit migration lama yang sudah dipakai database bersama/produksi, kecuali pemilik memastikan belum pernah diterapkan.
- Backup database dan `storage/app/private` sebelum rilis/migrasi. Uji pemulihan backup, bukan hanya keberadaan file backup.
- Harga, DP, tenor, total, jadwal, status bayar, dan stok dihitung/divalidasi server. Jangan menerima `$request->all()`.
- Update stok hanya lewat `StokService`, dalam transaksi, dengan mutasi audit.
- Jangan menonaktifkan global scope/Policy untuk “memperbaiki data hilang”. Pastikan cabang dan user yang aktif benar.
- Jangan hapus permanen transaksi uang atau memalsukan status pembayaran. Gunakan alur koreksi yang tercatat.
- Jangan commit `.env`, `APP_KEY`, password, data KTP, database dump, log yang berisi data sensitif, atau upload pelanggan.
- File kwitansi bersifat privat; jangan membuat symlink publik atau mengubahnya menjadi URL tanpa otorisasi.
- Jangan mengaktifkan kembali register publik: saat ini belum ada alur aman untuk menetapkan role/cabang admin.
- Jangan menganggap menu tersembunyi sebagai izin. Route dan Policy harus diuji terpisah.

### Peta file berdasarkan jenis perubahan

| Perubahan | Mulai periksa / ubah |
|---|---|
| Label/tata letak halaman | `resources/views/...`, komponen, `resources/css/app.css`. |
| URL/menu | `routes/web.php`, sidebar/navigation, controller yang dituju, test akses. |
| Input/validasi | `app/Http/Requests/*Request.php`, pesan Indonesia, controller, feature test. |
| Hak akses/cabang | `app/Policies/*Policy.php`, route middleware, `BelongsToCabang`, feature test lintas cabang. |
| Rumus kredit/jadwal | `KreditService.php`, `PenerbitanFakturController`, test jumlah total/tanggal 28-31. |
| Stok/pembelian | `StokService.php`, controller/request pembelian/barang, test mutasi dan stok kurang. |
| Kolom/tabel | migration baru, model fillable/cast/relasi, request/controller/view/test, prosedur deploy. |
| Kwitansi/cetak | `CetakController.php`, `resources/views/cetak`, `components/cetak-layout`, `config/cetak.php`, tes akses/file privat. |
| Peran/login | `routes/auth.php`, auth controller/request, seeder, policy/middleware, test. Jangan membuka register tanpa desain admin provisioning. |

### Perintah pengembangan aman

```powershell
# Dari akar proyek
php artisan about
php artisan route:list --except-vendor
php artisan view:cache
php artisan test --compact tests/Feature/Fase5Test.php
git diff --check
npm run build
```

Ganti nama test sesuai modul yang berubah. Untuk test DB lakukan pada database test. `view:cache` memeriksa kompilasi Blade; test HTTP tetap diperlukan untuk izin dan alur. Jika mengubah CSS/JS, `npm run build` diperlukan agar hasil produksi berubah. Jangan menjalankan semua test/build berulang tanpa alasan ketika token/waktu terbatas.

---

## 6. Perawatan rutin dan pemulihan masalah

### Setiap hari / sebelum penggunaan penting

- Pastikan aplikasi dapat login dan dashboard termuat.
- Periksa transaksi tertunda, stok anomali, dan tagihan jatuh tempo.
- Pastikan backup database serta berkas privat dibuat sesuai kebijakan pemilik.

### Sebelum setiap rilis

1. Catat commit/tag versi yang sedang berjalan.
2. Backup database dan `storage/app/private`; pastikan lokasi backup tidak web-public.
3. Terapkan migration non-destruktif dengan pemilik/administrator menyetujui waktu pemeliharaan.
4. Build aset (`npm ci`, `npm run build`) jika CSS/JS berubah.
5. Bersihkan dan buat ulang cache yang sesuai setelah update: `php artisan optimize:clear`, lalu `config:cache`, `route:cache`, `view:cache`.
6. Coba login pusat/cabang, pembatasan cabang, pembelian/mutasi, SO/survei, penerbitan faktur/stok, pembayaran, laporan, upload privat, dan cetak.
7. Pantau `storage/logs/laravel.log`. Jangan menyalin isi yang memuat data pelanggan ke tempat umum.

### Diagnostik awal programmer

- **HTTP 403:** policy/role/cabang; jangan menonaktifkan proteksi untuk melewati error.
- **HTTP 404 saat memakai record:** cek ID, soft delete, scope cabang, dan relasi. Jangan otomatis memakai `withoutGlobalScopes()`.
- **ParseError Blade:** jalankan `php artisan view:cache`, baca baris error view, periksa directive `@if/@endif`, `@error/@enderror`; lint hasil compiled view bila perlu.
- **Route baru tidak tampak:** `php artisan route:list --path=...`, kemudian cek route cache.
- **CSS lama:** cek manifest/build `public/build`, jalankan `npm run build`, refresh browser.
- **File template hilang:** cek setting `template_kwitansi_path`, disk local/private, izin tulis, dan backup file.
- **SQLSTATE / DB tidak tersambung:** cek nama koneksi/database di environment secara lokal; jangan cetak seluruh `.env` ke log/chat.
- **Stok tak cocok:** bandingkan saldo `stok_barangs` dengan `mutasi_stoks`; jangan ubah satu sisi secara manual. Koreksi melalui fitur/service yang menghasilkan mutasi.

### Kondisi rollback

Jika rilis gagal, hentikan input baru, catat error dan commit, lalu pulihkan sesuai prosedur backup. Rollback migration hanya aman bila `down()` tidak menghapus data penting. Jangan melakukan reset database atau reset Git (`reset --hard`) untuk mengatasi error produksi.

---

## 7. Rilis dan deploy

Panduan operasional lengkap ada di `docs/DEPLOY-CPANEL.md`. Ringkasnya: PHP minimal 8.2 dan ekstensi Laravel tersedia; domain mengarah ke `public/`; environment produksi memiliki `APP_DEBUG=false`, `APP_KEY` unik, HTTPS, kredensial DB produksi; folder `storage`/`bootstrap/cache` writable; aset `public/build` sudah dibuat; migration memakai `php artisan migrate --force` setelah backup.

**Dilarang di produksi:** `migrate:fresh`, akun demo, debug terbuka, `.env` di document root, database dump di `public/`, kredensial di Git, template kwitansi di URL publik.

Deploy nyata perlu akses hosting/domain/database pemilik dan uji smoke di server. Dokumen ini sendiri tidak menjalankan deploy.

---

## 8. Batas fitur dan hal yang harus dikonfirmasi

- Pembayaran parsial belum didukung.
- Laba/Rugi adalah margin MVP, bukan laporan akuntansi lengkap.
- Fase 2 di luar MVP mencakup retur lengkap, denda, akun collector/sales, notifikasi, dan pengembangan terkait; jangan anggap sudah aktif hanya karena model/tabel ada.
- Stok berkurang saat faktur diterbitkan sesuai implementasi saat ini; pemilik perlu mengonfirmasi perubahan kebijakan bila operasional menghendaki saat barang benar-benar terkirim.
- Template aktif otomatis berubah saat admin mengganti upload. Posisi presisi fisik tetap perlu verifikasi printer/kertas pengguna.
- Pengelolaan akun admin cabang belum tersedia sebagai CRUD user publik; jangan gunakan form karyawan sebagai pembuat akun.
- Status rinci fase dan keputusan terbaru tercatat di `docs/PROGRESS.md` dan aturan final proyek di `AGENTS.md`.

---

