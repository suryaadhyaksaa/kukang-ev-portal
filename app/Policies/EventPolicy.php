<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(User $user, Event $event): bool
    {
        return $user->is_active;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'admin']);
    }

    public function update(User $user, Event $event): bool
    {
        return $user->hasAnyRole(['super_admin', 'admin']);
    }

    public function generateQr(User $user, Event $event): bool
    {
        return $user->hasAnyRole(['super_admin', 'admin'])
            && ! in_array($event->status, ['cancelled', 'completed'], true);
    }
}
