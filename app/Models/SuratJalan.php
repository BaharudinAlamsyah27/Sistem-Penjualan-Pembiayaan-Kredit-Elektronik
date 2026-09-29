<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCabang;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SuratJalan extends Model
{
    use HasFactory, BelongsToCabang, SoftDeletes;

    protected $fillable = [
        'cabang_id',
        'sales_order_id',
        'no_ttb',
        'driver_id',
        'tgl_kirim',
        'nota',
        'status_verifikasi',
    ];

    protected function casts(): array
    {
        return [
            'tgl_kirim' => 'date',
        ];
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'driver_id');
    }
}
