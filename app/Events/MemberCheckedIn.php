<?php

namespace App\Events;

use App\Models\Attendance;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MemberCheckedIn implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $payload;

    public function __construct(Attendance $attendance)
    {
        $attendance->loadMissing(['member.division', 'member.position', 'event']);

        $this->payload = [
            'attendance_id' => $attendance->id,
            'event_id' => $attendance->event_id,
            'event_title' => $attendance->event->title,
            'nim' => $attendance->member->nim,
            'full_name' => $attendance->member->full_name,
            'division' => $attendance->member->division?->name ?? 'Umum',
            'position' => $attendance->member->position?->name ?? 'Anggota',
            'checked_in_at' => $attendance->checked_in_at->timezone('Asia/Jakarta')->format('H:i:s'),
            'status' => $attendance->status,
        ];
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('kukang-ev.live-monitoring'),
            new PrivateChannel('event.'.$this->payload['event_id']),
        ];
    }

    public function broadcastAs(): string
    {
        return 'member.checked-in';
    }
}
