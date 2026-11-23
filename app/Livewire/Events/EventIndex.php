<?php

namespace App\Livewire\Events;

use App\Models\ActivityLog;
use App\Models\Division;
use App\Models\Event;
use App\Models\Member;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Manajemen Kegiatan')]
class EventIndex extends Component
{
    use AuthorizesRequests, WithPagination;

    public string $search = '';
    public string $filterStatus = '';
    public bool $showModal = false;
    public ?string $editingEventId = null;

    public string $title = '';
    public string $description = '';
    public string $event_date = '';
    public string $start_time = '09:00';
    public string $end_time = '12:00';
    public string $location = '';
    public string $division_id = '';
    public string $pic_member_id = '';
    public string $status = 'upcoming';
    public string $attendance_open_at = '';
    public string $attendance_close_at = '';
    public string $participantMode = 'all_active'; // all_active | division_only | manual

    /** @var array<int, string> */
    public array $selectedMemberIds = [];

    public function mount(): void
    {
        $this->event_date = now()->toDateString();
    }

    public function openCreateModal(): void
    {
        $this->authorize('create', Event::class);
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEditModal(string $eventId): void
    {
        $event = Event::with('participants')->findOrFail($eventId);
        $this->authorize('update', $event);

        $this->editingEventId = $event->id;
        $this->title = $event->title;
        $this->description = $event->description ?? '';
        $this->event_date = $event->event_date->format('Y-m-d');
        $this->start_time = substr($event->start_time, 0, 5);
        $this->end_time = substr($event->end_time, 0, 5);
        $this->location = $event->location;
        $this->division_id = $event->division_id ?? '';
        $this->pic_member_id = $event->pic_member_id ?? '';
        $this->status = $event->status;
        $this->attendance_open_at = $event->attendance_open_at?->format('Y-m-d\TH:i') ?? '';
        $this->attendance_close_at = $event->attendance_close_at?->format('Y-m-d\TH:i') ?? '';
        $this->participantMode = 'manual';
        $this->selectedMemberIds = $event->participants->pluck('id')->all();
        $this->showModal = true;
    }

    public function saveEvent(): void
    {
        $event = $this->editingEventId ? Event::findOrFail($this->editingEventId) : null;

        if ($event) {
            $this->authorize('update', $event);
        } else {
            $this->authorize('create', Event::class);
        }

        $validated = $this->validate([
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:2000'],
            'event_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'location' => ['required', 'string', 'max:200'],
            'division_id' => ['nullable', 'exists:divisions,id'],
            'pic_member_id' => ['nullable', 'exists:members,id'],
            'status' => ['required', Rule::in(['upcoming', 'ongoing', 'completed', 'cancelled'])],
            'attendance_open_at' => ['nullable', 'date'],
            'attendance_close_at' => ['nullable', 'date', 'after:attendance_open_at'],
        ]);

        $payload = [
            ...$validated,
            'division_id' => $validated['division_id'] ?: null,
            'pic_member_id' => $validated['pic_member_id'] ?: null,
            'attendance_open_at' => $validated['attendance_open_at'] ?: null,
            'attendance_close_at' => $validated['attendance_close_at'] ?: null,
        ];

        if ($event) {
            $event->update($payload);
            ActivityLog::record('event.updated', "Memperbarui kegiatan: {$event->title}", Event::class, $event->id);
        } else {
            $event = Event::create([
                ...$payload,
                'event_code' => 'EVT-'.now()->format('Ymd').'-'.strtoupper(Str::random(4)),
                'created_by' => Auth::id(),
            ]);
            ActivityLog::record('event.created', "Membuat kegiatan baru: {$event->title}", Event::class, $event->id);
        }

        $participantIds = match ($this->participantMode) {
            'all_active' => Member::where('status', 'active')->pluck('id')->all(),
            'division_only' => Member::where('status', 'active')
                ->when($event->division_id, fn ($q) => $q->where('division_id', $event->division_id))
                ->pluck('id')
                ->all(),
            default => $this->selectedMemberIds,
        };

        $event->participants()->sync($participantIds);

        $this->showModal = false;
        $this->resetForm();
        session()->flash('success', "Kegiatan \"{$event->title}\" berhasil disimpan dengan ".count($participantIds).' peserta terdaftar.');
    }

    public function deleteEvent(string $eventId): void
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        abort_unless($user?->hasAnyRole(['super_admin', 'admin']), 403);

        $event = Event::findOrFail($eventId);

        if (method_exists($event, 'qrTokens')) {
            $event->qrTokens()->update(['is_active' => false]);
        }

        $title = $event->title;
        $id = $event->id;
        $event->delete();

        ActivityLog::record('event.deleted', "Menghapus kegiatan: {$title}", Event::class, $id);

        session()->flash('success', "Kegiatan \"{$title}\" berhasil dihapus.");
    }

    private function resetForm(): void
    {
        $this->reset([
            'editingEventId', 'title', 'description', 'location',
            'division_id', 'pic_member_id', 'attendance_open_at', 'attendance_close_at', 'selectedMemberIds',
        ]);
        $this->event_date = now()->toDateString();
        $this->start_time = '09:00';
        $this->end_time = '12:00';
        $this->status = 'upcoming';
        $this->participantMode = 'all_active';
    }

    public function render()
    {
        $events = Event::query()
            ->with(['division', 'pic'])
            ->withCount(['participants', 'attendances'])
            ->when($this->search, fn ($q) => $q->where('title', 'like', '%'.$this->search.'%')
                ->orWhere('event_code', 'like', '%'.$this->search.'%'))
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->orderByDesc('event_date')
            ->orderByDesc('start_time')
            ->paginate(10);

        $divisions = Division::where('is_active', true)->orderBy('name')->get();
        $activeMembers = Member::where('status', 'active')->orderBy('full_name')->get();

        return view('livewire.events.event-index', compact('events', 'divisions', 'activeMembers'));
    }
}
