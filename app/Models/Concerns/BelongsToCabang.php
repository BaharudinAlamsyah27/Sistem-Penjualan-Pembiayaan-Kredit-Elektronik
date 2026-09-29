<?php

namespace App\Models\Concerns;

use App\Models\Cabang;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

trait BelongsToCabang
{
    protected static function bootBelongsToCabang(): void
    {
        // Global scope: admin_pusat melihat semua, admin_cabang hanya cabangnya
        static::addGlobalScope('cabang', function (Builder $builder) {
            $user = Auth::user();
            if ($user && !$user->hasRole('admin_pusat')) {
                $builder->where($builder->getModel()->getTable() . '.cabang_id', $user->cabang_id);
            }
        });

        // Event creating: Isi cabang_id otomatis dari user yang login jika belum diset
        static::creating(function ($model) {
            if (!$model->cabang_id && Auth::check()) {
                $model->cabang_id = Auth::user()->cabang_id;
            }
        });
    }

    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class, 'cabang_id');
    }
}