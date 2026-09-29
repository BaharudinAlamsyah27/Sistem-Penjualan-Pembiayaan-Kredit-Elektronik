<?php

namespace Database\Seeders;

use App\Models\Barang;
use App\Models\Cabang;
use App\Models\Karyawan;
use App\Models\Pelanggan;
use App\Models\Setting;
use App\Models\StokBarang;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Roles (Spatie)
        $rolePusat = Role::firstOrCreate(['name' => 'admin_pusat']);
        $roleCabang = Role::firstOrCreate(['name' => 'admin_cabang']);

        // 2. Cabangs (Pusat, Gesi, Gemolong — sesuai AGENTS.md)
        $cabangPusat = Cabang::create([
            'kode' => 'CAB-PUSAT',
            'nama' => 'Pusat',
            'alamat' => 'Jl. Slamet Riyadi No. 45, Solo',
            'no_hp' => '081234567890',
            'aktif' => true,
        ]);

        $cabangGesi = Cabang::create([
            'kode' => 'CAB-GESI',
            'nama' => 'Gesi',
            'alamat' => 'Jl. Raya Gesi No. 12, Sragen',
            'no_hp' => '081234567891',
            'aktif' => true,
        ]);

        $cabangGemolong = Cabang::create([
            'kode' => 'CAB-GEMOLONG',
            'nama' => 'Gemolong',
            'alamat' => 'Jl. Raya Gemolong No. 8, Sragen',
            'no_hp' => '081234567892',
            'aktif' => true,
        ]);

        // 3. Users
        $adminPusat = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@nexa.com',
            'password' => Hash::make('password'),
            'cabang_id' => null,
            'aktif' => true,
        ]);
        $adminPusat->email_verified_at = now();
        $adminPusat->save();
        $adminPusat->assignRole($rolePusat);

        $adminGesi = User::create([
            'name' => 'Admin Cabang Gesi',
            'email' => 'gesi@nexa.com',
            'password' => Hash::make('password'),
            'cabang_id' => $cabangGesi->id,
            'aktif' => true,
        ]);
        $adminGesi->email_verified_at = now();
        $adminGesi->save();
        $adminGesi->assignRole($roleCabang);

        $adminGemolong = User::create([
            'name' => 'Admin Cabang Gemolong',
            'email' => 'gemolong@nexa.com',
            'password' => Hash::make('password'),
            'cabang_id' => $cabangGemolong->id,
            'aktif' => true,
        ]);
        $adminGemolong->email_verified_at = now();
        $adminGemolong->save();
        $adminGemolong->assignRole($roleCabang);

        // 4. Karyawan (tiap jabatan: sales, supervisor, kolektor, surveyor, driver)
        $karyawans = [
            ['cabang_id' => $cabangPusat->id, 'kode' => 'KRY-PS-01', 'nama' => 'Andi Pratama', 'jabatan' => 'sales', 'no_hp' => '0811111111', 'area' => 'Area Pusat'],
            ['cabang_id' => $cabangPusat->id, 'kode' => 'KRY-PS-02', 'nama' => 'Hendra Wijaya', 'jabatan' => 'supervisor', 'no_hp' => '0811111112', 'area' => 'Area Pusat'],
            ['cabang_id' => $cabangPusat->id, 'kode' => 'KRY-PS-03', 'nama' => 'Budi Santoso', 'jabatan' => 'kolektor', 'no_hp' => '0811111113', 'area' => 'Area Pusat'],
            ['cabang_id' => $cabangPusat->id, 'kode' => 'KRY-PS-04', 'nama' => 'Eko Prasetyo', 'jabatan' => 'surveyor', 'no_hp' => '0811111114', 'area' => 'Area Pusat'],
            ['cabang_id' => $cabangPusat->id, 'kode' => 'KRY-PS-05', 'nama' => 'Doni Saputra', 'jabatan' => 'driver', 'no_hp' => '0811111115', 'area' => 'Area Pusat'],

            ['cabang_id' => $cabangGesi->id, 'kode' => 'KRY-GS-01', 'nama' => 'Andi', 'jabatan' => 'sales', 'no_hp' => '0812222221', 'area' => 'Area Gesi'],
            ['cabang_id' => $cabangGesi->id, 'kode' => 'KRY-GS-02', 'nama' => 'Budi', 'jabatan' => 'kolektor', 'no_hp' => '0812222222', 'area' => 'Area Gesi'],
            ['cabang_id' => $cabangGesi->id, 'kode' => 'KRY-GS-03', 'nama' => 'Cahyo Wibowo', 'jabatan' => 'surveyor', 'no_hp' => '0812222223', 'area' => 'Area Gesi'],
            ['cabang_id' => $cabangGesi->id, 'kode' => 'KRY-GS-04', 'nama' => 'Dedi Setiawan', 'jabatan' => 'driver', 'no_hp' => '0812222224', 'area' => 'Area Gesi'],
            ['cabang_id' => $cabangGesi->id, 'kode' => 'KRY-GS-05', 'nama' => 'Surya Dinata', 'jabatan' => 'supervisor', 'no_hp' => '0812222225', 'area' => 'Area Gesi'],

            ['cabang_id' => $cabangGemolong->id, 'kode' => 'KRY-GM-01', 'nama' => 'Rudi Hartono', 'jabatan' => 'sales', 'no_hp' => '0813333331', 'area' => 'Area Gemolong'],
            ['cabang_id' => $cabangGemolong->id, 'kode' => 'KRY-GM-02', 'nama' => 'Siti Lestari', 'jabatan' => 'kolektor', 'no_hp' => '0813333332', 'area' => 'Area Gemolong'],
            ['cabang_id' => $cabangGemolong->id, 'kode' => 'KRY-GM-03', 'nama' => 'Agus Salim', 'jabatan' => 'surveyor', 'no_hp' => '0813333333', 'area' => 'Area Gemolong'],
            ['cabang_id' => $cabangGemolong->id, 'kode' => 'KRY-GM-04', 'nama' => 'Wahyu Nugroho', 'jabatan' => 'driver', 'no_hp' => '0813333334', 'area' => 'Area Gemolong'],
            ['cabang_id' => $cabangGemolong->id, 'kode' => 'KRY-GM-05', 'nama' => 'Bambang Suryo', 'jabatan' => 'supervisor', 'no_hp' => '0813333335', 'area' => 'Area Gemolong'],
        ];

        foreach ($karyawans as $k) {
            Karyawan::create($k);
        }

        // 5. Pelanggan Contoh (Ahmad Riyadi - Jl. Merdeka No 10)
        Pelanggan::create([
            'cabang_id' => $cabangPusat->id,
            'kode' => 'PLG-SL-001',
            'nama' => 'Ahmad Riyadi',
            'no_hp' => '081333444555',
            'no_ktp' => '3372010101900001',
            'alamat' => 'Jl. Merdeka No 10',
            'kabupaten' => 'Kota Surakarta',
            'kecamatan' => 'Banjarsari',
            'catatan' => 'Pelanggan tetap',
        ]);

        Pelanggan::create([
            'cabang_id' => $cabangGesi->id,
            'kode' => 'PLG-GS-001',
            'nama' => 'Bpk. Sudirman',
            'no_hp' => '081333444556',
            'no_ktp' => '3314010101850002',
            'alamat' => 'Jl. Garuda No. 5',
            'kabupaten' => 'Sragen',
            'kecamatan' => 'Sragen Kota',
            'catatan' => 'Pelanggan referensi sales Andi',
        ]);

        Pelanggan::create([
            'cabang_id' => $cabangGemolong->id,
            'kode' => 'PLG-GM-001',
            'nama' => 'Ibu Siti Aminah',
            'no_hp' => '081333444557',
            'no_ktp' => '3314020202920003',
            'alamat' => 'Jl. Mawar No. 12',
            'kabupaten' => 'Sragen',
            'kecamatan' => 'Tanon',
            'catatan' => 'Dekat pasar Tanon',
        ]);

        // 6. Barang Contoh (Kulkas 2 Pintu Merk XYZ harga 4.000.000)
        $kulkas = Barang::create([
            'kode' => 'BRG-KLK-01',
            'nama' => 'Kulkas 2 Pintu Merk XYZ',
            'merk' => 'XYZ',
            'kategori' => 'Elektronik',
            'harga_beli' => 3200000,
            'harga_jual' => 4000000,
            'aktif' => true,
        ]);

        $tv = Barang::create([
            'kode' => 'BRG-TV-01',
            'nama' => 'TV LED 43 Inch Merk ABC',
            'merk' => 'ABC',
            'kategori' => 'Elektronik',
            'harga_beli' => 2500000,
            'harga_jual' => 3500000,
            'aktif' => true,
        ]);

        $mesinCuci = Barang::create([
            'kode' => 'BRG-MC-01',
            'nama' => 'Mesin Cuci 1 Tabung Merk DEF',
            'merk' => 'DEF',
            'kategori' => 'Elektronik',
            'harga_beli' => 2000000,
            'harga_jual' => 2800000,
            'aktif' => true,
        ]);

        // 7. Stok Barang per Cabang
        $cabangs = [$cabangPusat, $cabangGesi, $cabangGemolong];
        $barangs = [$kulkas, $tv, $mesinCuci];

        foreach ($cabangs as $cabang) {
            foreach ($barangs as $barang) {
                StokBarang::create([
                    'cabang_id' => $cabang->id,
                    'barang_id' => $barang->id,
                    'qty' => 10,
                ]);
            }
        }

        // 8. Setting Default
        Setting::set('dp_hitung_angsuran_pertama', '0');
    }
}
