<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCabang;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pelanggan extends Model
{
    use HasFactory, BelongsToCabang, SoftDeletes;

    protected $fillable = [
        'cabang_id',
        'kode',
        'nama',
        'no_hp',
        'no_ktp',
        'alamat',
        'kabupaten',
        'kecamatan',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'no_ktp' => 'encrypted',
        ];
    }

    public function salesOrders(): HasMany
    {
        return $this->hasMany(SalesOrder::class);
    }
}
