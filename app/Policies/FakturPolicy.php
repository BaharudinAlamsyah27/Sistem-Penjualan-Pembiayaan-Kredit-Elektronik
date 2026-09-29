<?php

namespace App\Policies;

use App\Models\Faktur;
use App\Models\User;

class FakturPolicy
{
    public function create(User $user): bool
    {
        return $user->hasRole(['admin_pusat', 'admin_cabang']);
    }

    public function view(User $user, Faktur $faktur): bool
    {
        return $user->hasRole('admin_pusat') || ($user->hasRole('admin_cabang') && $user->cabang_id === $faktur->cabang_id);
    }
}
