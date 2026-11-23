<?php

namespace App\Policies;

use App\Models\Division;
use App\Models\User;

class DivisionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'admin']);
    }

    public function manage(User $user, ?Division $division = null): bool
    {
        return $user->hasAnyRole(['super_admin', 'admin']);
    }
}
