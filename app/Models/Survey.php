<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCabang;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Survey extends Model
{
    use HasFactory, BelongsToCabang, SoftDeletes;

    protected $fillable = [
        'cabang_id',
        'sales_order_id',
        'surveyor_id',
        'tanggal',
        'hasil',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class)->withTrashed();
    }

    public function surveyor(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'surveyor_id')->withTrashed();
    }
}
