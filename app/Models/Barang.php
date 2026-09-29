<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Barang extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty()->useLogName('barang');
    }

    protected $fillable = [
        'kode',
        'nama',
        'merk',
        'kategori',
        'harga_beli',
        'harga_jual',
        'aktif',
    ];

    protected function casts(): array
    {
        return [
            'harga_beli' => 'decimal:2',
            'harga_jual' => 'decimal:2',
            'aktif' => 'boolean',
        ];
    }

    public function stokBarangs(): HasMany
    {
        return $this->hasMany(StokBarang::class);
    }

    public function mutasiStoks(): HasMany
    {
        return $this->hasMany(MutasiStok::class);
    }
}
