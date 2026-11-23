<?php

namespace App\Livewire\Members;

use App\Models\ActivityLog;
use App\Models\Attendance;
use App\Models\Contribution;
use App\Models\Documentation;
use App\Models\Member;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Profil & Riwayat Kontribusi Anggota')]
class MemberProfile extends Component
{
    use AuthorizesRequests;

    public Member $member;

    public function mount(?Member $member = null): void
    {
        /** @var \App\Models\User $authUser */
        $authUser = Auth::user();

        $targetMember = ($member && $member->exists)
            ? $member
            : $authUser->member()->firstOrFail();

        $this->authorize('view', $targetMember);
        $this->member = $targetMember->load(['division', 'position', 'user.roles']);
    }

    public function render()
    {
        // Denominator persentase kehadiran: jumlah kegiatan di mana anggota terdaftar sebagai peserta
        $registeredEventIds = $this->member->registeredEvents()->pluck('events.id');
        $registeredEventsCount = $registeredEventIds->count();

        $totalAttendances = Attendance::where('member_id', $this->member->id)->count();
        $attendedRegisteredCount = $registeredEventsCount > 0
            ? Attendance::where('member_id', $this->member->id)
            ->whereIn('event_id', $registeredEventIds)
            ->count()
            : 0;

        $attendancePercentage = $registeredEventsCount > 0
            ? round(($attendedRegisteredCount / $registeredEventsCount) * 100, 1)
            : 0.0;

        $totalContributions = Contribution::where('member_id', $this->member->id)->count();
        $approvedContributions = Contribution::where('member_id', $this->member->id)
            ->where('status', 'approved')
            ->count();
        $totalDocumentations = Documentation::where('uploaded_by_member_id', $this->member->id)->count();

        $recentContributions = Contribution::with('event')
            ->where('member_id', $this->member->id)
            ->latest('submitted_at')
            ->limit(5)
            ->get();

        $recentDocs = Documentation::with('event')
            ->where('uploaded_by_member_id', $this->member->id)
            ->latest()
            ->limit(6)
            ->get();

        $timeline = ActivityLog::where('user_id', $this->member->user_id)
            ->orWhere(function ($q) {
                $q->where('entity_type', Contribution::class)
                    ->whereIn('entity_id', Contribution::where('member_id', $this->member->id)->pluck('id'));
            })
            ->latest('created_at')
            ->limit(20)
            ->get();

        return view('livewire.members.member-profile', compact(
            'registeredEventsCount',
            'totalAttendances',
            'attendancePercentage',
            'totalContributions',
            'approvedContributions',
            'totalDocumentations',
            'recentContributions',
            'recentDocs',
            'timeline'
        ));
    }
}
