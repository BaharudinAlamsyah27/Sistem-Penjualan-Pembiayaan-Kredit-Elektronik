# NexaERP — Pedoman Proyek untuk Agent

> Dokumen ini adalah **sumber kebenaran tunggal**. Baca seluruhnya sebelum mengerjakan fase apa pun. Jika instruksi pengguna bertentangan dengan dokumen ini, tanyakan dulu, lalu perbarui dokumen ini setelah diputuskan.

---

## 0. Cara Kerja Agent (WAJIB)

1. **Kerjakan fase secara berurutan.** Jika pengguna secara eksplisit meminta beberapa fase, tuntaskan satu per satu sesuai urutan; jangan melompat atau menggabungkan kriteria selesai.
2. **Sebelum menulis kode**, buat rencana singkat (daftar file yang akan dibuat/diubah). Untuk fase besar, tunggu persetujuan jika mode Planning tersedia.
3. **Setelah selesai**, jalankan `php artisan migrate:fresh --seed` (jika ada perubahan DB) dan pengujian. Jika pengguna meminta penghematan test, jalankan hanya test inti yang relevan dengan fase aktif; laporkan batas verifikasi dengan singkat.
4. **Jangan mengubah file di luar lingkup fase.** Jika menemukan masalah di fase lain, catat di `docs/PROGRESS.md` bagian "Catatan/Masalah", jangan langsung diperbaiki.
5. **Perbarui `docs/PROGRESS.md`** di akhir setiap fase (centang tugas, tulis keputusan baru).
6. **Commit git per fase** dengan pesan `fase-N: ringkasan`.
7. **Hemat token:** tampilkan di chat hanya ringkasan dan potongan kode penting. Jangan mencetak ulang file panjang.
8. **Jangan mengarang aturan bisnis.** Jika ada yang tidak tercantum di dokumen ini, ajukan pertanyaan (maksimal 3, dikelompokkan) atau ambil asumsi paling sederhana dan tuliskan di `docs/PROGRESS.md`.
9. **Semua teks antarmuka, pesan validasi, dan label memakai Bahasa Indonesia.** Nama tabel/kolom/kode memakai bahasa Indonesia seperti di dokumen ini.
10. **Format uang:** `Rp 4.000.000` (titik ribuan). Simpan di database sebagai `decimal(15,2)`; jangan float.

---

## 1. Ringkasan Proyek

- **Nama:** NexaERP
- **Jenis:** sistem penjualan **kredit** barang elektronik (kulkas, TV, dll) untuk satu pemilik dengan **3 cabang** (bisa bertambah).
- **Beda dengan kasir biasa:** pembayaran dicicil dengan tenor dan jatuh tempo; ada sales, surveyor, dan kolektor.
- **Target:** MVP siap dipakai dalam sekitar 1 minggu. Lingkup dikunci; fitur di luar MVP masuk Fase 2.
- **Deploy:** hosting cPanel (bukan VPS).

### Alur bisnis utama

```
Sales Order (sales) -> Survei (surveyor) -> [Layak] -> Surat Jalan & Faktur + Nota DP
   -> Jadwal angsuran dibuat otomatis -> Barang Terkirim
   -> Kolektor menagih tiap jatuh tempo -> Penerimaan Angsuran -> Lunas
```

---

## 2. Keputusan Final (jangan diubah tanpa persetujuan pengguna)

| # | Keputusan |
|---|---|
| 1 | Satu aplikasi, satu database, banyak cabang. Kolom `cabang_id` di setiap tabel master/transaksi (kecuali `cabangs`). **Tidak** memakai subdomain per cabang. |
| 2 | Rumus kredit mengikuti desain NexaERP (model margin), lihat bagian 7. |
| 3 | DP **dicatat sebagai pembayaran tersendiri**. Ada pengaturan `dp_hitung_angsuran_pertama` (default `false`); bila `true`, DP dianggap angsuran ke-1 dan jadwal dibuat `tenor - 1` baris. |
| 4 | Hanya **2 peran login**: `admin_pusat` (semua cabang) dan `admin_cabang` (hanya cabangnya). Sales, kolektor, surveyor, driver, supervisor **hanya data karyawan** (tanpa akun), dipilih lewat dropdown. |
| 5 | Supervisor hanya sebagai jabatan data, tanpa hierarki di sistem. |
| 6 | Tabel transaksi = header; tabel `angsurans` = detail per bulan. |
| 7 | Nilai kredit (dp, tenor, bunga, pokok, margin, angsuran) **disimpan sebagai snapshot** di `sales_orders`, tidak dibaca ulang dari master. |
| 8 | Semua nominal **dihitung ulang di server**; angka dari browser hanya untuk tampilan. |

---

## 3. Stack & Lingkungan

- **Lokal:** Laragon (Windows), PHP 8.3, MySQL, Node LTS, Composer.
- **Backend:** Laravel 11.
- **Frontend:** Blade + **Tailwind CSS** + Alpine.js (via Vite). Grafik: Chart.js.
- **Paket:**
  - `laravel/breeze` (stack Blade) — autentikasi
  - `spatie/laravel-permission` — peran
  - `spatie/laravel-activitylog` — jejak perubahan
  - `barryvdh/laravel-dompdf` — opsional, untuk PDF nota (cetak utama tetap lewat halaman HTML `@media print`)
- **Testing:** Pest atau PHPUnit (bawaan Laravel).
- **Struktur kode:**
  - `app/Services/` untuk logika bisnis (`KreditService`, `AngsuranGenerator`, `NomorDokumenService`, `StokService`)
  - `app/Models/Concerns/BelongsToCabang.php`
  - `app/Policies/`, `app/Http/Requests/`
  - `docs/design/` untuk gambar desain, `docs/PROGRESS.md` untuk catatan kemajuan

---

## 4. Desain UI (tiru persis gambar di `docs/design/`)

Gambar acuan: `docs/design/dashboard.png` (Dashboard Analytics) dan `docs/design/sales-order.png` (Buat Sales Order & Skema Kredit Baru). **Ambil warna, ukuran font, jarak, dan radius langsung dari gambar**; jangan menebak. Nilai di bawah hanya perkiraan awal.

### 4.1 Layout umum
- **Sidebar kiri** (lebar sekitar 220px, putih, garis pemisah tipis). Logo teks "NexaERP" berwarna biru. Label grup huruf kapital kecil abu-abu. Item aktif: teks biru tebal + garis biru di sisi kiri.
- **Header atas:** judul halaman di kiri, badge peran ("Super Admin" untuk `admin_pusat`, nama cabang untuk `admin_cabang`) dengan ikon di kanan.
- **Latar halaman** abu-abu sangat terang; kartu putih, border tipis, radius sedang, bayangan halus.
- Token warna (awal): `--primary: #2f5fb3` (perkiraan biru panel), latar `#f4f6fa`, teks `#1f2937`, hijau untuk "lunas/layak", oranye untuk "menunggu".
- Responsif: sidebar menjadi drawer di layar kecil.

### 4.2 Menu sidebar (urutan persis)
```
MENU UTAMA
  Dashboard Analytics
SALES & KREDIT
  Data Pelanggan
  Sales Order (SO)
  Survey Kelayakan
  Penerimaan Angsuran
INVENTORI & LOGISTIK
  Data Barang
  Pembelian & Retur
  Surat Jalan (TTB)
KEUANGAN (LAPORAN)
  Laba / Rugi
  Laporan Tagihan
MASTER & SETUP
  Karyawan & Akses
```
Tambahan MVP yang tidak ada di gambar: **Data Cabang** (di grup Master & Setup, hanya `admin_pusat`).

### 4.3 Dashboard Analytics
- **4 kartu:** Laba Bersih (bulan ini, dengan keterangan pertumbuhan), Total Piutang Berjalan (dengan peringatan "N pelanggan jatuh tempo"), Angsuran Masuk Hari Ini, Sales Order Menunggu Survey (dengan keterangan "Tugaskan surveyor segera").
- **Tabel "Tagihan Angsuran Jatuh Tempo Hari Ini":** No. Faktur, Nama Pelanggan (dengan "Angsuran ke X dari Y"), Nominal Tagihan, Kolektor (Sales) + area, Status (badge: "Menunggu Pembayaran" oranye, "Lunas Hari Ini" hijau).
- Definisi angka (MVP):
  - Total Piutang Berjalan = jumlah `nominal` angsuran berstatus bukan lunas.
  - Angsuran Masuk Hari Ini = jumlah `nominal_dibayar` dengan `tgl_bayar` hari ini.
  - SO Menunggu Survey = jumlah SO berstatus `survey` dengan hasil `menunggu`.
  - **Laba Bersih:** definisi belum final. Untuk MVP tampilkan margin dari angsuran yang lunas bulan ini lewat `LabaService` terpisah agar mudah diganti. Catat sebagai asumsi.

### 4.4 Halaman "Buat Sales Order & Skema Kredit Baru" (wizard 4 langkah)
Indikator langkah: **1. Input SO, 2. Survey & Hitung Kredit, 3. Surat Jalan & Faktur, 4. Barang Terkirim.** Langkah aktif memakai titik biru.

- **Kolom kiri "Informasi Transaksi":** Pelanggan (pilih dari master, tampil nama + alamat), Barang yang Dipesan, Harga Jual Barang (Rp), Hasil Survey Lapangan (contoh: "LAYAK - Lanjutkan ke Penjualan").
- **Kolom kanan "Pengaturan Skema Kredit":** Uang Muka (DP), Tenor (Angsuran) dalam bulan, Bunga Margin (%) per bulan.
- **Panel biru ringkasan (dihitung langsung di browser dengan Alpine, dihitung ulang di server saat simpan):** Total Harga Barang, Down Payment (DP) bertanda minus, Pokok Piutang, Total Margin/Bunga (N bln) bertanda plus.
- **Catatan tulisan tangan pada desain (asumsi, tandai untuk dikonfirmasi):**
  - Langkah 3 memuat isian **Driver** dan **Nota**; langkah 4 menandai barang terkirim.
  - Surat Jalan (TTB) memiliki status **Terverifikasi**, dengan isian Tgl Kirim, Kolektor, Driver, BPV.
  - Pembelian & Retur memiliki isian **Cashmoney**, Qty, Harga.

---

## 5. Multi-Cabang

Trait `BelongsToCabang` dipasang di semua model bertabel `cabang_id`:

```php
trait BelongsToCabang {
    protected static function bootBelongsToCabang() {
        static::addGlobalScope('cabang', function ($q) {
            $u = auth()->user();
            if ($u && !$u->hasRole('admin_pusat')) {
                $q->where($q->getModel()->getTable().'.cabang_id', $u->cabang_id);
            }
        });
        static::creating(function ($m) {
            if (!$m->cabang_id && auth()->check()) {
                $m->cabang_id = auth()->user()->cabang_id;
            }
        });
    }
}
```

- `admin_pusat` memiliki `cabang_id = null`. Saat membuat data, ia **wajib memilih cabang** di form (dropdown), dan ada filter cabang di daftar/laporan.
- Semua query laporan wajib memakai scope ini. Dilarang memakai `withoutGlobalScopes()` kecuali untuk `admin_pusat` dan diberi komentar alasan.
- Master `barangs` bersifat global; **stok per cabang** ada di `stok_barangs`.

---

## 6. Skema Database

Semua tabel: `id`, `timestamps`; tabel keuangan/transaksi juga `softDeletes`. Uang = `decimal(15,2)`.

| Tabel | Kolom penting |
|---|---|
| `cabangs` | kode (unique), nama, alamat, no_hp, aktif |
| `users` | name, email, password, cabang_id (nullable), aktif. Peran lewat spatie |
| `karyawans` | cabang_id, kode, nama, jabatan (`sales`,`supervisor`,`kolektor`,`surveyor`,`driver`), no_hp, area, aktif |
| `pelanggans` | cabang_id, kode, nama, no_hp, no_ktp (**cast `encrypted`**), alamat, **kabupaten**, **kecamatan**, catatan |
| `barangs` | kode (unique), nama, merk, kategori, harga_beli, harga_jual, aktif |
| `stok_barangs` | cabang_id, barang_id, qty. Unique (cabang_id, barang_id) |
| `mutasi_stoks` | cabang_id, barang_id, tipe (`masuk`,`keluar`,`retur`,`penyesuaian`), qty, referensi_type, referensi_id, tanggal, keterangan, user_id |
| `pembelians` | cabang_id, no_pembelian, tanggal, supplier, metode (`cashmoney`,`tempo`), total, user_id |
| `pembelian_details` | pembelian_id, barang_id, qty, harga |
| `returs` | cabang_id, no_retur, tanggal, jenis (`retur_pembelian`,`retur_penjualan`), referensi_id, alasan |
| `sales_orders` | cabang_id, no_so, tanggal, pelanggan_id, barang_id, qty (default 1), sales_id (karyawan), harga_jual, dp, tenor, bunga_persen, pokok_piutang, total_margin, total_kredit, angsuran_per_bulan, status (`input`,`survey`,`disetujui`,`ditolak`,`terkirim`,`batal`), created_by |
| `surveys` | cabang_id, sales_order_id, surveyor_id, tanggal, hasil (`menunggu`,`layak`,`tidak_layak`), catatan |
| `fakturs` | cabang_id, sales_order_id, no_faktur, tanggal, tgl_dp, status (`aktif`,`lunas`,`batal`) |
| `angsurans` | cabang_id, faktur_id, **jenis** (`dp`,`angsuran`), angsuran_ke, nominal, jatuh_tempo, kolektor_id (karyawan, nullable), status (`belum`,`menunggu_pembayaran`,`lunas`), tgl_bayar, nominal_dibayar, metode, catatan, dicatat_oleh. Unique (faktur_id, jenis, angsuran_ke). Index (cabang_id, jatuh_tempo, status) |
| `surat_jalans` | cabang_id, sales_order_id, no_ttb, driver_id (karyawan), tgl_kirim, nota, status_verifikasi (`menunggu`,`terverifikasi`) |
| `nomor_dokumens` | cabang_id, jenis, periode (YYYYMM), terakhir. Unique (cabang_id, jenis, periode) |
| `settings` | key (unique), value |

Seeder wajib: 1 `admin_pusat`, 1 cabang contoh + 1 `admin_cabang`, beberapa karyawan tiap jabatan, pelanggan contoh (mis. "Ahmad Riyadi - Jl. Merdeka No 10"), barang contoh (mis. "Kulkas 2 Pintu Merk XYZ" harga 4.000.000), dan setting default.

---

## 7. Logika Bisnis

### 7.1 Rumus kredit (`App\Services\KreditService`)
```php
public static function hitung(float $harga, float $dp, int $tenor, float $bungaPersen): array
{
    $pokok  = $harga - $dp;
    $margin = $pokok * ($bungaPersen / 100) * $tenor;   // bunga flat per bulan
    $total  = $pokok + $margin;
    return [
        'pokok_piutang' => $pokok,
        'total_margin'  => $margin,
        'total_kredit'  => $total,
        'angsuran'      => round($total / $tenor),
    ];
}
```
**Contoh uji wajib:** harga 4.000.000, DP 800.000, tenor 12, bunga 1,5% -> pokok 3.200.000, margin 576.000, total 3.776.000, angsuran 314.667.

**Pembulatan:** karena 314.667 x 12 = 3.776.004, angsuran **terakhir** = `total_kredit - jumlah angsuran sebelumnya` (jadi 314.663), agar total tepat.

### 7.2 Validasi
- DP: `>= 0` dan `< harga_jual`. Tenor: bilangan bulat `1..60`. Bunga: `0..100`.
- SO hanya bisa lanjut ke Surat Jalan & Faktur jika survei `layak`. Survei `tidak_layak` mengubah SO menjadi `ditolak`.

### 7.3 Penerbitan faktur (satu `DB::transaction`)
1. Kunci dan cek stok barang di cabang tersebut (`lockForUpdate`); tolak jika kurang.
2. Buat nomor dokumen lewat `NomorDokumenService` (kunci baris `nomor_dokumens`). Format faktur: `INV-YYYYMM-001`, urut per cabang per bulan. Format SO: `SO-YYYYMM-001`.
3. Buat `fakturs`.
4. Buat baris `angsurans` jenis `dp` (ke-0, status `lunas`, nominal = DP, `tgl_bayar` = `tgl_dp`).
5. Generate jadwal angsuran:
```php
$jumlah = $so->tenor - (setting('dp_hitung_angsuran_pertama') ? 1 : 0);
$total  = $so->total_kredit;
$sudah  = 0;
for ($i = 1; $i <= $jumlah; $i++) {
    $nominal = ($i === $jumlah) ? $total - $sudah : $so->angsuran_per_bulan;
    $sudah  += $nominal;
    Angsuran::create([
        'faktur_id'   => $faktur->id,
        'jenis'       => 'angsuran',
        'angsuran_ke' => $i,
        'nominal'     => $nominal,
        'jatuh_tempo' => Carbon::parse($faktur->tgl_dp)->addMonthsNoOverflow($i),
        'status'      => 'belum',
    ]);
}
```
(Bila `dp_hitung_angsuran_pertama = true`, baris DP diberi `angsuran_ke = 1` dan jadwal menyesuaikan; total pembulatan tetap dijaga.)
6. Catat mutasi stok `keluar` dan kurangi `stok_barangs`.
7. Ubah status SO menjadi `disetujui`, lalu `terkirim` saat langkah 4 dikonfirmasi.

### 7.3b Aturan jatuh tempo
Jatuh tempo mengikuti **tanggal bayar DP**, bulan berikutnya di tanggal yang sama. Pakai `addMonthsNoOverflow` (31 Jan -> 28/29 Feb, bukan lompat ke Maret).

### 7.4 Penerimaan angsuran
- Kolektor (dipilih dari dropdown karyawan) dan admin mencatat pembayaran: `nominal_dibayar`, `tgl_bayar`, `metode`, `catatan`.
- Status angsuran menjadi `lunas`. Bila semua angsuran lunas, faktur menjadi `lunas`.
- Pembayaran parsial tidak didukung di MVP (tandai sebagai pertanyaan terbuka).
- Angsuran yang sudah `lunas` hanya bisa diedit `admin_pusat`, dan tercatat di activity log.

### 7.5 Stok
Semua perubahan stok **wajib** lewat `StokService` (mencatat `mutasi_stoks`). Dilarang mengubah `stok_barangs.qty` langsung dari controller.

---

## 8. Peran & Hak Akses

| Peran | Akses |
|---|---|
| `admin_pusat` | Semua menu, semua cabang, Data Cabang, Karyawan & Akses, Laba/Rugi |
| `admin_cabang` | Semua menu operasional **hanya untuk cabangnya**; tidak bisa mengelola cabang atau user |

- Menu sidebar tampil sesuai izin. **Menyembunyikan menu tidak cukup**: pasang Policy dan middleware di setiap route.
- Struktur peran memakai spatie agar mudah ditambah kelak (mis. kolektor punya akun).

---

## 9. Keamanan

- Form Request untuk semua input; `$fillable` ketat; jangan pernah `$request->all()` ke `create()`.
- Otorisasi lewat Policy (model yang punya `cabang_id`).
- `no_ktp` memakai cast `encrypted`.
- Rate limit login; kebijakan password minimal 8 karakter.
- Activity log pada `sales_orders`, `fakturs`, `angsurans`, `barangs`, `stok_barangs`.
- Soft delete untuk data keuangan; jangan hapus permanen.
- Produksi: `APP_DEBUG=false`, HTTPS, `.env` tidak boleh dapat diakses publik, backup database terjadwal.
- Cegah SQL injection: hanya Eloquent/Query Builder dengan binding; larangan `DB::raw` dengan input pengguna.

---

## 10. Cetak Nota / Kwitansi

- Klien memiliki **template kwitansi fisik** yang posisinya harus pas. Minta pengguna mengunggah foto/scan ke `docs/design/kwitansi.jpg` sebelum Fase 8.
- Buat halaman cetak khusus (`resources/views/cetak/*.blade.php`) dengan CSS `@page` dan posisi elemen memakai satuan **mm** (`position: absolute`), tanpa sidebar/header.
- Semua koordinat elemen dikumpulkan di satu file konfigurasi (`config/cetak.php`) agar mudah disetel berulang kali tanpa membongkar view.
- Jenis cetak: Nota DP, Faktur/Invoice, Kwitansi Angsuran, Surat Jalan.
- Sediakan halaman pratinjau dengan garis bantu (grid) opsional untuk kalibrasi.

---

## 11. Rencana Fase (kerjakan berurutan)

Setiap fase punya **kriteria selesai (KS)**. Fase dianggap selesai bila semua KS terpenuhi.

### Fase 0 — Persiapan
- Buat proyek Laravel 11, konfigurasi `.env` (MySQL Laragon), install Breeze, spatie permission & activitylog, Tailwind/Alpine/Vite.
- Buat `docs/PROGRESS.md`, `docs/design/`, dan inisialisasi git.
- **KS:** `php artisan serve` (atau URL Laragon) menampilkan halaman login; `npm run build` sukses.

### Fase 1 — Database, Seeder, Peran
- Semua migration di bagian 6, model + relasi, trait `BelongsToCabang`, seeder, peran `admin_pusat`/`admin_cabang`, tabel `settings` + helper `setting()`.
- **KS:** `migrate:fresh --seed` sukses; login sebagai kedua peran berhasil; test membuktikan `admin_cabang` tidak bisa melihat data cabang lain.

### Fase 2 — Layout & Dashboard
- Layout Blade sesuai bagian 4 (sidebar, header, badge peran), menu sesuai izin, halaman Dashboard dengan 4 kartu + tabel jatuh tempo (data seeder/dummy boleh dulu).
- **KS:** tampilan dashboard dan sidebar **mirip** `docs/design/dashboard.png` (bandingkan berdampingan); responsif di layar kecil.

### Fase 3 — Data Master
- CRUD: Cabang (pusat saja), Karyawan & Akses, Data Pelanggan (dengan kabupaten/kecamatan, filter/sortir), Data Barang (+ stok per cabang).
- Pembelian sederhana sebagai jalur stok masuk (`cashmoney`/`tempo`), lewat `StokService`.
- **KS:** semua CRUD berfungsi dengan validasi; stok bertambah lewat pembelian dan tercatat di `mutasi_stoks`.

### Fase 4 — Sales Order & Survei
- Wizard langkah 1-2 sesuai bagian 4.4: input SO, tugaskan surveyor, input hasil survei, hitung kredit langsung (Alpine) + hitung ulang di server.
- `KreditService` + unit test contoh 4.000.000 / 800.000 / 12 / 1,5%.
- **KS:** nilai panel biru sama dengan hasil server; SO `tidak_layak` berubah `ditolak`; tampilan mirip `sales-order.png`.

### Fase 5 — Faktur & Jadwal Angsuran
- Wizard langkah 3-4: surat jalan sederhana (driver, tgl kirim, nota), penerbitan faktur sesuai 7.3, generator jadwal, stok keluar, konfirmasi terkirim.
- `NomorDokumenService`.
- **KS:** feature test: total seluruh angsuran = `total_kredit` tepat; jatuh tempo benar (termasuk tanggal 31); stok berkurang; transaksi gagal total bila stok kurang.

### Fase 6 — Penerimaan Angsuran & Tagihan
- Halaman Penerimaan Angsuran (cari faktur/pelanggan, catat pembayaran), dashboard memakai data nyata, Laporan Tagihan (filter bulan, kolektor, kabupaten/kecamatan, status).
- **KS:** pertanyaan "angsuran bulan X milik siapa saja" terjawab lewat laporan; faktur otomatis `lunas` saat semua angsuran lunas.

### Fase 7 — Laporan Dasar
- Laporan penjualan per sales, tagihan per kolektor, ekspor CSV/Excel sederhana. `LabaService` versi MVP.
- **KS:** angka laporan cocok dengan data uji.

### Fase 8 — Cetak
- Halaman cetak sesuai bagian 10 dan `config/cetak.php`, pratinjau kalibrasi.
- **KS:** hasil cetak pas di template kwitansi klien (uji beberapa kali).

### Fase 9 — Pengerasan & Deploy
- Review keamanan bagian 9, seed data uji, panduan pengguna singkat (`docs/PANDUAN.md`), panduan deploy cPanel (bagian 12).
- **KS:** semua test lulus; aplikasi berjalan di cPanel dengan `APP_DEBUG=false`.

### Fase 2 Proyek (di luar MVP, JANGAN dikerjakan kecuali diminta)
Laba/Rugi lengkap, Pembelian & Retur lengkap (retur barang), verifikasi TTB dengan BPV, grafik dashboard, denda keterlambatan, pembayaran parsial, akun untuk kolektor/sales, notifikasi jatuh tempo.

---

## 12. Deploy ke cPanel (panduan untuk Fase 9)

1. Pilih versi PHP >= 8.2 di cPanel; aktifkan ekstensi umum (mbstring, openssl, pdo_mysql, tokenizer, xml, ctype, json, bcmath, fileinfo).
2. Arahkan **document root domain ke folder `public/`**. Jika tidak bisa diubah: letakkan isi `public/` di `public_html` dan sesuaikan path pada `index.php` ke folder aplikasi yang berada **di luar** `public_html`.
3. Jalankan `composer install --no-dev --optimize-autoloader` dan `npm run build` di lokal, lalu unggah hasilnya bila server tidak punya akses SSH/Composer.
4. Buat database + user di cPanel; isi `.env` produksi (`APP_ENV=production`, `APP_DEBUG=false`, `APP_URL`, kredensial DB). Jalankan migration lewat terminal cPanel atau impor SQL.
5. `php artisan key:generate`, `storage:link`, `config:cache`, `route:cache`, `view:cache`.
6. Pastikan folder `storage` dan `bootstrap/cache` dapat ditulis, dan `.env` tidak dapat diakses lewat web.
7. Aktifkan HTTPS (AutoSSL) dan atur backup database.

---

## 13. Standar Kode & Testing

- Ikuti PSR-12; controller tipis, logika di `Services`.
- Test wajib: `KreditService`, generator angsuran (termasuk pembulatan dan tanggal 29/30/31), isolasi cabang, otorisasi peran, penerbitan faktur (transaksi gagal bila stok kurang).
- Jangan menaruh rahasia di repositori; sertakan `.env.example`.

---

## 14. Pertanyaan Terbuka (jangan diasumsikan diam-diam)

1. Definisi **Laba Bersih** di dashboard dan laporan Laba/Rugi.
2. Ada tidaknya **denda keterlambatan** dan aturan **penarikan barang** jika macet.
3. Apakah pembayaran angsuran **parsial** diizinkan.
4. Arti pasti isian **BPV** dan **Nota** pada surat jalan (dari coretan desain).
5. Kapan stok berkurang: saat faktur terbit (asumsi saat ini) atau saat barang terkirim.
6. Template kwitansi/nota klien (menunggu file dari pengguna).

---

## 15. Template Permintaan per Fase (untuk pengguna)

```
Kerjakan Fase N sesuai GEMINI.md. Baca bagian 0, 2, dan bagian yang relevan saja.
Buat rencana singkat dulu, lalu kerjakan. Setelah selesai: jalankan test, perbarui docs/PROGRESS.md,
commit, dan laporkan ringkas (apa dibuat, cara uji, yang belum selesai).
```
