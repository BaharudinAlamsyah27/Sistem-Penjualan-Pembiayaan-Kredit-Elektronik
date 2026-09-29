# Panduan Singkat NexaERP

## Masuk dan hak akses

- Admin pusat: `admin@nexa.com` (akses seluruh cabang dan pengaturan template).
- Admin cabang: gunakan akun cabang yang dibuat oleh seeder; akses dibatasi ke cabangnya.
- Ganti kata sandi awal sebelum aplikasi dipakai. Karyawan operasional dipilih dari master karyawan dan tidak memiliki akun login.

## Alur transaksi

1. Lengkapi cabang, karyawan, pelanggan, barang, dan saldo stok.
2. Catat pembelian untuk menambah stok; gunakan penyesuaian stok hanya untuk koreksi fisik dan selalu masukkan alasan.
3. Buat Sales Order, tugaskan surveyor, lalu simpan hasil survei. SO yang layak dapat diteruskan menjadi faktur dan surat jalan.
4. Catat pembayaran angsuran penuh dari menu Penerimaan Angsuran. Pembayaran parsial belum tersedia.
5. Gunakan tombol cetak pada detail SO atau daftar angsuran. Browser membuka dialog cetak; pilih ukuran kertas sesuai dokumen.

## Template kwitansi

Admin pusat membuka menu **Template Kwitansi**, mengunggah JPG/PNG, lalu memakai pratinjau garis bantu. File disimpan privat. Setiap halaman kwitansi membaca template aktif dari pengaturan, sehingga unggahan pengganti langsung digunakan tanpa mengubah kode atau membuka VS Code. Posisi awal berada di `config/cetak.php`; sesuaikan bila hasil cetak bergeser.

## Dukungan operasional

Jangan hapus transaksi keuangan. Koreksi pembayaran lunas dilakukan admin pusat agar tercatat. Simpan backup database harian di lokasi terpisah.
