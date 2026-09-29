<?php

namespace App\Policies;

use App\Models\Pelanggan;
use App\Models\User;

class PelangganPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin_pusat', 'admin_cabang']);
    }

    public function view(User $user, Pelanggan $pelanggan): bool
    {
        return $user->hasRole('admin_pusat') || ($user->hasRole('admin_cabang') && $user->cabang_id === $pelanggan->cabang_id);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['admin_pusat', 'admin_cabang']);
    }

    public function update(User $user, Pelanggan $pelanggan): bool
    {
        return $this->view($user, $pelanggan);
    }

    public function delete(User $user, Pelanggan $pelanggan): bool
    {
        return $this->view($user, $pelanggan);
    }
}
