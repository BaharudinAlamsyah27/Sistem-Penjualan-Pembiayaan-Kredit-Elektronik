<?php

return [
    // Ukuran dan posisi dalam milimeter; sesuaikan setelah cetak percobaan pada printer klien.
    'kwitansi' => [
        'width' => 297,
        'height' => 187,
        'background' => ['x' => 0, 'y' => 0, 'width' => 297, 'height' => 187],
        'fields' => [
            'tanggal' => ['x' => 242, 'y' => 18, 'width' => 45],
            'faktur' => ['x' => 242, 'y' => 25, 'width' => 45],
            'pelanggan' => ['x' => 52, 'y' => 41, 'width' => 220],
            'jumlah' => ['x' => 52, 'y' => 51, 'width' => 220],
            'baris' => ['x' => 23, 'y' => 83, 'width' => 250, 'height' => 7],
            'total' => ['x' => 235, 'y' => 146, 'width' => 50],
        ],
    ],
    'faktur' => ['width' => 210, 'height' => 297],
    'nota_dp' => ['width' => 210, 'height' => 297],
    'surat_jalan' => ['width' => 210, 'height' => 297],
];
