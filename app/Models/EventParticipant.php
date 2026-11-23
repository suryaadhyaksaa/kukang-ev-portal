<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\Pivot;

class EventParticipant extends Pivot
{
    use HasUlids;

    protected $table = 'event_participants';

    protected $keyType = 'string';

    public $incrementing = false;
}
