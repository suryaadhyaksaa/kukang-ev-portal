<?php

namespace App\Policies;

use App\Models\Documentation;
use App\Models\User;

class DocumentationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(User $user, Documentation $documentation): bool
    {
        if ($user->hasAnyRole(['super_admin', 'admin'])) {
            return true;
        }

        // File dengan visibility private_member hanya boleh diakses pemiliknya & Admin
        if ($documentation->visibility === 'private_member') {
            return $user->member && $user->member->id === $documentation->uploaded_by_member_id;
        }

        return $user->is_active;
    }

    public function create(User $user): bool
    {
        return $user->is_active && $user->member && $user->member->status === 'active';
    }

    public function delete(User $user, Documentation $documentation): bool
    {
        if ($user->hasAnyRole(['super_admin', 'admin'])) {
            return true;
        }

        return $user->member && $user->member->id === $documentation->uploaded_by_member_id;
    }
}
