<?php

namespace App\Events;

use App\Models\Contribution;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContributionSubmitted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $payload;

    public function __construct(Contribution $contribution)
    {
        $contribution->loadMissing(['member.division', 'event']);

        $this->payload = [
            'contribution_id' => $contribution->id,
            'event_title' => $contribution->event->title,
            'member_name' => $contribution->member->full_name,
            'nim' => $contribution->member->nim,
            'division' => $contribution->member->division?->name ?? 'Umum',
            'category' => $contribution->category,
            'status' => $contribution->status,
            'submitted_at' => $contribution->submitted_at->timezone('Asia/Jakarta')->format('d M Y H:i'),
        ];
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('kukang-ev.live-monitoring'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'contribution.submitted';
    }
}
