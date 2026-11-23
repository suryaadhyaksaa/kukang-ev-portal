<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Documentation extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'event_id',
        'contribution_id',
        'uploaded_by_member_id',
        'title',
        'original_filename',
        'stored_path',
        'disk',
        'mime_type',
        'file_size',
        'visibility',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function contribution(): BelongsTo
    {
        return $this->belongsTo(Contribution::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'uploaded_by_member_id');
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function isVideo(): bool
    {
        return str_starts_with($this->mime_type, 'video/');
    }

    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    public function formattedSize(): string
    {
        if ($this->file_size >= 1048576) {
            return number_format($this->file_size / 1048576, 2).' MB';
        }

        return number_format(max(1, $this->file_size / 1024), 1).' KB';
    }
}
