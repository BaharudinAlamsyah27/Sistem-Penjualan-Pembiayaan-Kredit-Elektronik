<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait BelongsToCabang
{
    protected static function bootBelongsToCabang(): void
    {
        // Global scope: Filter otomatis berdasarkan cabang_id user
        static::addGlobalScope('cabang', function (Builder $builder) {
            if (Auth::check()) {
                /** @var \App\Models\User $user */
                $user = Auth::user();
                if (!$user->hasRole('admin_pusat') && $user->cabang_id) {
                    $builder->where($builder->getModel()->getTable() . '.cabang_id', $user->cabang_id);
                }
            }
        });

        // Event creating: Isi cabang_id otomatis jika tidak diisi manual
        static::creating(function ($model) {
            if (!$model->cabang_id && Auth::check()) {
                /** @var \App\Models\User $user */
                $user = Auth::user();
                $model->cabang_id = $user->cabang_id;
            }
        });
    }
}