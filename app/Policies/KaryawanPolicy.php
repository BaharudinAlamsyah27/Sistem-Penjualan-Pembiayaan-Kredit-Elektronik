<?php

namespace App\Policies;

use App\Models\Karyawan;
use App\Models\User;

class KaryawanPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin_pusat', 'admin_cabang']);
    }

    public function view(User $user, Karyawan $karyawan): bool
    {
        return $user->hasRole('admin_pusat') || ($user->hasRole('admin_cabang') && $user->cabang_id === $karyawan->cabang_id);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['admin_pusat', 'admin_cabang']);
    }

    public function update(User $user, Karyawan $karyawan): bool
    {
        return $this->view($user, $karyawan);
    }

    public function delete(User $user, Karyawan $karyawan): bool
    {
        return $this->view($user, $karyawan);
    }
}
