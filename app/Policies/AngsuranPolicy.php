<?php

namespace App\Policies;

use App\Models\Angsuran;
use App\Models\User;

class AngsuranPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin_pusat', 'admin_cabang']);
    }

    public function view(User $user, Angsuran $angsuran): bool
    {
        return $user->hasRole('admin_pusat')
            || ($user->hasRole('admin_cabang') && $user->cabang_id === $angsuran->cabang_id);
    }

    public function receive(User $user, Angsuran $angsuran): bool
    {
        return $this->view($user, $angsuran);
    }

    public function update(User $user, Angsuran $angsuran): bool
    {
        return $user->hasRole('admin_pusat');
    }
}
