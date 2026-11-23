<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MsGraphSyncLog extends Model
{
    use HasUlids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $guarded = [];

    public function triggerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }
}
