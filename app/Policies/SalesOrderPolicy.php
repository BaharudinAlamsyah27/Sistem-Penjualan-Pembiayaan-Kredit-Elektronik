<?php

namespace App\Policies;

use App\Models\SalesOrder;
use App\Models\User;

class SalesOrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin_pusat', 'admin_cabang']);
    }

    public function view(User $user, SalesOrder $salesOrder): bool
    {
        return $user->hasRole('admin_pusat') || ($user->hasRole('admin_cabang') && $user->cabang_id === $salesOrder->cabang_id);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['admin_pusat', 'admin_cabang']);
    }
}
