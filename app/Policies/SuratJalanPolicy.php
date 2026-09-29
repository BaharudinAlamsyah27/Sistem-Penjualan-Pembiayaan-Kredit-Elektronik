<?php

namespace App\Policies;

use App\Models\SuratJalan;
use App\Models\User;

class SuratJalanPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin_pusat', 'admin_cabang']);
    }

    public function view(User $user, SuratJalan $suratJalan): bool
    {
        return $user->hasRole('admin_pusat') || ($user->hasRole('admin_cabang') && $user->cabang_id === $suratJalan->cabang_id);
    }

    public function verify(User $user, SuratJalan $suratJalan): bool
    {
        return $this->view($user, $suratJalan);
    }
}
