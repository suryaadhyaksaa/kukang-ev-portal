<?php

namespace App\Policies;

use App\Models\Member;
use App\Models\User;

class MemberPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'admin']);
    }

    public function view(User $user, Member $member): bool
    {
        return $user->hasAnyRole(['super_admin', 'admin']) || $user->id === $member->user_id;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'admin']);
    }

    public function update(User $user, Member $member): bool
    {
        // Admin/Super Admin dapat mengubah data anggota; anggota biasa hanya boleh update profil terbatas
        return $user->hasAnyRole(['super_admin', 'admin']);
    }

    public function deactivate(User $user, Member $member): bool
    {
        // Cegah admin menonaktifkan akun dirinya sendiri
        return $user->hasAnyRole(['super_admin', 'admin']) && $user->id !== $member->user_id;
    }
}
