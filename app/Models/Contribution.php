<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contribution extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    public const CATEGORIES = [
        'Hardware Development',
        'Electrical System',
        'Software Development',
        'Mechanical Development',
        'Administration',
        'Sponsorship',
        'Public Relations',
        'Event Management',
        'Documentation',
        'Research & Development',
        'Other',
    ];

    public const STATUSES = [
        'pending' => 'Pending',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'revision_required' => 'Revision Required',
    ];

    protected $fillable = [
        'event_id',
        'member_id',
        'category',
        'work_description',
        'work_output',
        'obstacles',
        'status',
        'reviewer_notes',
        'verified_by',
        'verified_at',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
            'submitted_at' => 'datetime',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function documentations(): HasMany
    {
        return $this->hasMany(Documentation::class);
    }
}
