<?php

namespace App\Livewire\Members;

use App\Models\ActivityLog;
use App\Models\Member;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Detail Anggota')]
class MemberDetail extends Component
{
    use AuthorizesRequests;

    public Member $member;

    public function mount(Member $member): void
    {
        $this->authorize('view', $member);
        $this->member = $member->load(['division', 'position', 'user.roles']);
    }

    public function render()
    {
        $activities = ActivityLog::where('user_id', $this->member->user_id)
            ->latest('created_at')
            ->limit(20)
            ->get();

        return view('livewire.members.member-detail', compact('activities'));
    }
}
