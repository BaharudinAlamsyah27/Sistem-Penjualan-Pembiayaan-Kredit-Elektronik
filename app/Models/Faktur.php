<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCabang;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Faktur extends Model
{
    use HasFactory, BelongsToCabang, LogsActivity, SoftDeletes;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty()->useLogName('faktur');
    }

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
        return $this->belongsTo(SalesOrder::class)->withTrashed();
    }

    public function angsurans(): HasMany
    {
        return $this->hasMany(Angsuran::class);
    }
}
