<?php

return [
    'accepted' => ':attribute harus disetujui.',
    'array' => ':attribute harus berupa daftar.',
    'boolean' => ':attribute harus bernilai ya atau tidak.',
    'date' => ':attribute harus berupa tanggal yang valid.',
    'distinct' => ':attribute memiliki nilai yang duplikat.',
    'exists' => ':attribute yang dipilih tidak valid.',
    'in' => ':attribute yang dipilih tidak valid.',
    'integer' => ':attribute harus berupa bilangan bulat.',
    'max' => [
        'numeric' => ':attribute tidak boleh lebih dari :max.',
        'string' => ':attribute tidak boleh lebih dari :max karakter.',
        'array' => ':attribute tidak boleh memiliki lebih dari :max item.',
    ],
    'min' => [
        'numeric' => ':attribute minimal :min.',
        'string' => ':attribute minimal :min karakter.',
        'array' => ':attribute harus memiliki sedikitnya :min item.',
    ],
    'numeric' => ':attribute harus berupa angka.',
    'required' => ':attribute wajib diisi.',
    'regex' => 'Format :attribute tidak valid.',
    'string' => ':attribute harus berupa teks.',
    'unique' => ':attribute sudah digunakan.',
    'attributes' => [
        'cabang_id' => 'cabang', 'kode' => 'kode', 'nama' => 'nama', 'no_hp' => 'nomor telepon',
        'alamat' => 'alamat', 'kabupaten' => 'kabupaten', 'kecamatan' => 'kecamatan', 'jabatan' => 'jabatan',
        'tanggal' => 'tanggal', 'supplier' => 'pemasok', 'metode' => 'metode pembayaran',
        'items' => 'daftar barang', 'items.*.barang_id' => 'barang', 'items.*.qty' => 'jumlah', 'items.*.harga' => 'harga',
        'harga_beli' => 'harga beli', 'harga_jual' => 'harga jual', 'no_ktp' => 'nomor KTP',
        'dp' => 'uang muka', 'tenor' => 'tenor', 'bunga_persen' => 'bunga margin', 'qty' => 'jumlah barang',
        'pelanggan_id' => 'pelanggan', 'barang_id' => 'barang', 'sales_id' => 'sales', 'surveyor_id' => 'surveyor',
        'hasil' => 'hasil survei',
    ],
];
