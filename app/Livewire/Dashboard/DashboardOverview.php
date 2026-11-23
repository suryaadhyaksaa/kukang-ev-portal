<?php

namespace App\Livewire\Dashboard;

use App\Models\Attendance;
use App\Models\Contribution;
use App\Models\Division;
use App\Models\Documentation;
use App\Models\Event;
use App\Models\Member;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Dashboard Operasional')]
class DashboardOverview extends Component
{
    #[On('echo-private:kukang-ev.live-monitoring,.member.checked-in')]
    #[On('echo-private:kukang-ev.live-monitoring,.contribution.submitted')]
    #[On('echo-private:kukang-ev.live-monitoring,.contribution.verified')]
    public function handleRealtimeUpdate(): void
    {
        // Memicu re-render otomatis saat terjadi event real-time
    }

    public function render()
    {
        /** @var User $user */
        $user = Auth::user();
        $isAdmin = $user->hasAnyRole(['super_admin', 'admin']);

        $latestCheckIns = Attendance::with(['member.division', 'event'])
            ->latest('checked_in_at')
            ->limit(6)
            ->get();

        $recentContributions = Contribution::with(['member.division', 'event'])
            ->when(! $isAdmin, fn ($q) => $q->where('member_id', $user->member?->id))
            ->latest('submitted_at')
            ->limit(6)
            ->get();

        return view('livewire.dashboard.dashboard-overview', [
            'isAdmin' => $isAdmin,
            ...$this->getKpiMetrics(),
            ...$this->getTrendChartData(),
            ...$this->getDivisionChartData(),
            'latestCheckIns' => $latestCheckIns,
            'recentContributions' => $recentContributions,
        ]);
    }

    /** @return array<string, mixed> */
    private function getKpiMetrics(): array
    {
        $eventsWithParticipants = Event::withCount(['participants', 'attendances'])
            ->having('participants_count', '>', 0)
            ->get();

        $avgAttendanceRate = $eventsWithParticipants->isNotEmpty()
            ? round($eventsWithParticipants->avg(fn ($e) => min(100, ($e->attendances_count / $e->participants_count) * 100)), 1)
            : 0.0;

        return [
            'activeMembersCount' => Member::where('status', 'active')->count(),
            'totalEventsCount' => Event::count(),
            'upcomingEventsCount' => Event::whereIn('status', ['upcoming', 'ongoing'])->count(),
            'totalAttendancesCount' => Attendance::count(),
            'avgAttendanceRate' => $avgAttendanceRate,
            'approvedContributionsCount' => Contribution::where('status', 'approved')->count(),
            'pendingContributionsCount' => Contribution::where('status', 'pending')->count(),
            'totalDocumentationsCount' => Documentation::count(),
        ];
    }

    /** @return array<string, array<int, mixed>> */
    private function getTrendChartData(): array
    {
        $months = collect(range(5, 0))->map(fn (int $i) => Carbon::now()->subMonths($i));

        return [
            'trendLabels' => $months->map(fn (Carbon $d) => $d->translatedFormat('M Y'))->all(),
            'trendAttendances' => $months->map(
                fn (Carbon $d) => Attendance::whereYear('checked_in_at', $d->year)
                    ->whereMonth('checked_in_at', $d->month)
                    ->count()
            )->all(),
            'trendContributions' => $months->map(
                fn (Carbon $d) => Contribution::whereYear('submitted_at', $d->year)
                    ->whereMonth('submitted_at', $d->month)
                    ->count()
            )->all(),
        ];
    }

    /** @return array<string, array<int, mixed>> */
    private function getDivisionChartData(): array
    {
        $divisions = Division::where('is_active', true)
            ->withCount(['members' => fn ($q) => $q->where('status', 'active')])
            ->orderBy('name')
            ->get();

        return [
            'divisionLabels' => $divisions->pluck('code')->all(),
            'divisionMemberCounts' => $divisions->pluck('members_count')->all(),
            'divisionContributionCounts' => $divisions->map(function ($div) {
                /** @var Division $div */
                return Contribution::whereHas('member', fn ($q) => $q->where('division_id', $div->id))->count();
            })->all(),
        ];
    }
}
