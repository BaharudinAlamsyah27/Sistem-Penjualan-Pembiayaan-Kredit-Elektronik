<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCabang;
use Illuminate\Database\Eloquent\Model;

class StokBarang extends Model
{
    use BelongsToCabang;

    protected $fillable = ['cabang_id', 'barang_id', 'stok'];
}