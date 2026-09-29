<?php

namespace App\Policies;

use App\Models\Barang;
use App\Models\User;

class BarangPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin_pusat', 'admin_cabang']);
    }

    public function view(User $user, Barang $barang): bool
    {
        return $user->hasRole(['admin_pusat', 'admin_cabang']);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['admin_pusat', 'admin_cabang']);
    }

    public function update(User $user, Barang $barang): bool
    {
        return $this->view($user, $barang);
    }

    public function delete(User $user, Barang $barang): bool
    {
        return $this->view($user, $barang);
    }
}
