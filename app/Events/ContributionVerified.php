<?php

namespace App\Events;

use App\Models\Contribution;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContributionVerified implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $payload;

    public function __construct(Contribution $contribution)
    {
        $contribution->loadMissing(['member', 'event']);

        $this->payload = [
            'contribution_id' => $contribution->id,
            'member_user_id' => $contribution->member->user_id,
            'event_title' => $contribution->event->title,
            'status' => $contribution->status,
            'reviewer_notes' => $contribution->reviewer_notes,
        ];
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('kukang-ev.live-monitoring'),
            new PrivateChannel('user.'.$this->payload['member_user_id']),
        ];
    }

    public function broadcastAs(): string
    {
        return 'contribution.verified';
    }
}
