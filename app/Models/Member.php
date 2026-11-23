<?php
// app/Models/Member.php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Member extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'division_id',
        'position_id',
        'nim',
        'full_name',
        'email',
        'phone',
        'cohort_year',
        'avatar_path',
        'joined_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'joined_at' => 'date',
            'cohort_year' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function registeredEvents(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_participants')
            ->using(EventParticipant::class)
            ->withPivot(['id', 'is_mandatory'])
            ->withTimestamps();
    }

    public function attendances(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function contributions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Contribution::class);
    }

    public function documentations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Documentation::class, 'uploaded_by_member_id');
    }
}
