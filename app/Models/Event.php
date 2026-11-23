<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'event_code',
        'title',
        'description',
        'event_date',
        'start_time',
        'end_time',
        'location',
        'division_id',
        'pic_member_id',
        'status',
        'attendance_open_at',
        'attendance_close_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'attendance_open_at' => 'datetime',
            'attendance_close_at' => 'datetime',
        ];
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function pic(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'pic_member_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(Member::class, 'event_participants')
            ->using(EventParticipant::class)
            ->withPivot(['id', 'is_mandatory'])
            ->withTimestamps();
    }

    public function qrTokens(): HasMany
    {
        return $this->hasMany(EventQrToken::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function isAttendanceWindowOpen(): bool
    {
        if (in_array($this->status, ['cancelled', 'completed'], true)) {
            return false;
        }

        $now = now();

        if ($this->attendance_open_at && $now->lt($this->attendance_open_at)) {
            return false;
        }

        if ($this->attendance_close_at && $now->gt($this->attendance_close_at)) {
            return false;
        }

        return true;
    }

    public function contributions(): HasMany
    {
        return $this->hasMany(Contribution::class);
    }

    public function documentations(): HasMany
    {
        return $this->hasMany(Documentation::class);
    }
}
