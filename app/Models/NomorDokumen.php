<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCabang;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NomorDokumen extends Model
{
    use HasFactory, BelongsToCabang;

    protected $fillable = [
        'cabang_id',
        'jenis',
        'periode',
        'terakhir',
    ];
}
