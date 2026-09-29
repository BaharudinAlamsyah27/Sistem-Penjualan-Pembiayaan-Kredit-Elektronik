# Panduan Deploy cPanel

1. Gunakan PHP 8.2 atau lebih baru dan aktifkan `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, dan `fileinfo`.
2. Siapkan database MySQL dan user khusus. Arahkan document root domain ke folder `public/` aplikasi.
3. Unggah source dan hasil build frontend (`public/build`). Pasang dependency produksi dengan `composer install --no-dev --optimize-autoloader` bila Composer tersedia.
4. Buat `.env` produksi di akar aplikasi: `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://domain-anda`, `APP_KEY` unik, dan kredensial database cPanel. Jangan letakkan `.env` di `public_html`.
5. Jalankan `php artisan migrate --force` setelah backup dan verifikasi koneksi. Jangan menjalankan `migrate:fresh` pada data produksi.
6. Pastikan `storage` serta `bootstrap/cache` dapat ditulis. Buat `storage:link` hanya untuk berkas publik; template kwitansi tetap berada di disk privat.
7. Jalankan `php artisan config:cache`, `route:cache`, dan `view:cache`. Aktifkan HTTPS/AutoSSL dan uji login, isolasi cabang, alur transaksi, upload template, dan hasil cetak.
8. Jadwalkan backup database dan `storage/app/private` terenkripsi, batasi akses backup, dan lakukan uji pemulihan.

## Rilis frontend

Jalankan `npm ci` dan `npm run build` di mesin pengembang, lalu unggah `public/build` bersama manifest. Jangan unggah `node_modules`.

## Catatan

Akun demo dari seeder hanya untuk lokal. Buat akun produksi dan kata sandi baru secara aman sebelum aplikasi dipakai. Matikan `APP_DEBUG` di produksi.
