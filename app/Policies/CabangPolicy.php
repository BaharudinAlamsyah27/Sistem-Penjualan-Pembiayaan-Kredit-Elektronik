<?php

namespace App\Policies;

use App\Models\Cabang;
use App\Models\User;

class CabangPolicy
{
    public function before(User $user): ?bool
    {
        return $user->hasRole('admin_pusat') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, Cabang $cabang): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Cabang $cabang): bool
    {
        return false;
    }

    public function delete(User $user, Cabang $cabang): bool
    {
        return false;
    }
}
