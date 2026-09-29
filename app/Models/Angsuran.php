<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCabang;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Angsuran extends Model
{
    use HasFactory, BelongsToCabang, SoftDeletes;

    protected $fillable = [
        'cabang_id',
        'faktur_id',
        'jenis',
        'angsuran_ke',
        'nominal',
        'jatuh_tempo',
        'kolektor_id',
        'status',
        'tgl_bayar',
        'nominal_dibayar',
        'metode',
        'catatan',
        'dicatat_oleh',
    ];

    protected function casts(): array
    {
        return [
            'nominal' => 'decimal:2',
            'jatuh_tempo' => 'date',
            'tgl_bayar' => 'date',
            'nominal_dibayar' => 'decimal:2',
        ];
    }

    public function faktur(): BelongsTo
    {
        return $this->belongsTo(Faktur::class);
    }

    public function kolektor(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'kolektor_id');
    }

    public function dicatatOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }
}
