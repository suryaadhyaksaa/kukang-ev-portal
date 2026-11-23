<?php

namespace App\Policies;

use App\Models\Contribution;
use App\Models\User;

class ContributionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(User $user, Contribution $contribution): bool
    {
        if ($user->hasAnyRole(['super_admin', 'admin'])) {
            return true;
        }

        return $user->member && $user->member->id === $contribution->member_id;
    }

    public function create(User $user): bool
    {
        return $user->is_active && $user->member && $user->member->status === 'active';
    }

    public function update(User $user, Contribution $contribution): bool
    {
        // Anggota hanya dapat mengedit kontribusi miliknya sendiri selama status pending atau revision_required
        if ($user->member && $user->member->id === $contribution->member_id) {
            return in_array($contribution->status, ['pending', 'revision_required'], true);
        }

        return false;
    }

    public function delete(User $user, Contribution $contribution): bool
    {
        if ($user->hasAnyRole(['super_admin', 'admin'])) {
            return true;
        }

        return $user->member
            && $user->member->id === $contribution->member_id
            && $contribution->status === 'pending';
    }

    public function verify(User $user, Contribution $contribution): bool
    {
        // Hanya Super Admin / Admin yang dapat memverifikasi kontribusi
        return $user->hasAnyRole(['super_admin', 'admin']);
    }
}
