<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCabang;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Faktur extends Model
{
    use HasFactory, BelongsToCabang, SoftDeletes;

    protected $fillable = [
        'cabang_id',
        'sales_order_id',
        'no_faktur',
        'tanggal',
        'tgl_dp',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'tgl_dp' => 'date',
        ];
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function angsurans(): HasMany
    {
        return $this->hasMany(Angsuran::class);
    }
}
