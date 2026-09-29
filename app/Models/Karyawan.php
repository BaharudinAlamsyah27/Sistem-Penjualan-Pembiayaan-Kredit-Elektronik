<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCabang;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Karyawan extends Model
{
    use BelongsToCabang, SoftDeletes;

    protected $fillable = ['cabang_id', 'nik', 'nama', 'jabatan', 'no_hp'];
}