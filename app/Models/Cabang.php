<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cabang extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode',
        'nama',
        'alamat',
        'no_hp',
        'aktif',
    ];

    protected function casts(): array
    {
        return [
            'aktif' => 'boolean',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function karyawans(): HasMany
    {
        return $this->hasMany(Karyawan::class);
    }

    public function pelanggans(): HasMany
    {
        return $this->hasMany(Pelanggan::class);
    }

    public function stokBarangs(): HasMany
    {
        return $this->hasMany(StokBarang::class);
    }

    public function salesOrders(): HasMany
    {
        return $this->hasMany(SalesOrder::class);
    }

    public function fakturs(): HasMany
    {
        return $this->hasMany(Faktur::class);
    }
}