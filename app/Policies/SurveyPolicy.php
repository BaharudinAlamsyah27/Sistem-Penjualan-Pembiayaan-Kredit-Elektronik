<?php

namespace App\Policies;

use App\Models\Survey;
use App\Models\User;

class SurveyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin_pusat', 'admin_cabang']);
    }

    public function view(User $user, Survey $survey): bool
    {
        return $user->hasRole('admin_pusat') || ($user->hasRole('admin_cabang') && $user->cabang_id === $survey->cabang_id);
    }

    public function update(User $user, Survey $survey): bool
    {
        return $this->view($user, $survey);
    }
}
