<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('kukang-ev.live-monitoring', function (User $user) {
    return $user->hasAnyRole(['super_admin', 'admin']);
});

Broadcast::channel('event.{eventId}', function (User $user) {
    return $user->is_active;
});

Broadcast::channel('user.{userId}', function (User $user, string $userId) {
    return $user->id === $userId;
});
