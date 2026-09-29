<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCabang;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesOrder extends Model
{
    use HasFactory, BelongsToCabang, SoftDeletes;

    protected $fillable = [
        'cabang_id',
        'no_so',
        'tanggal',
        'pelanggan_id',
        'barang_id',
        'qty',
        'sales_id',
        'harga_jual',
        'dp',
        'tenor',
        'bunga_persen',
        'pokok_piutang',
        'total_margin',
        'total_kredit',
        'angsuran_per_bulan',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'harga_jual' => 'decimal:2',
            'dp' => 'decimal:2',
            'bunga_persen' => 'decimal:2',
            'pokok_piutang' => 'decimal:2',
            'total_margin' => 'decimal:2',
            'total_kredit' => 'decimal:2',
            'angsuran_per_bulan' => 'decimal:2',
        ];
    }

    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class)->withTrashed();
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class)->withTrashed();
    }

    public function sales(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'sales_id')->withTrashed();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function survey(): HasOne
    {
        return $this->hasOne(Survey::class);
    }

    public function faktur(): HasOne
    {
        return $this->hasOne(Faktur::class);
    }

    public function suratJalan(): HasOne
    {
        return $this->hasOne(SuratJalan::class);
    }
}
