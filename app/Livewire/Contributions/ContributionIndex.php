<?php

namespace App\Livewire\Contributions;

use App\Models\Contribution;
use App\Models\Division;
use App\Models\Event;
use App\Services\ContributionService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Contribution Tracker')]
class ContributionIndex extends Component
{
    use AuthorizesRequests, WithFileUploads, WithPagination;

    public string $search = '';
    public string $filterStatus = '';
    public string $filterCategory = '';
    public string $filterDivision = '';

    // Form Submit / Edit Anggota
    public bool $showFormModal = false;
    public ?string $editingContributionId = null;
    public string $event_id = '';
    public string $category = 'Software Development';
    public string $work_description = '';
    public string $work_output = '';
    public string $obstacles = '';

    /** @var array<int, mixed> */
    public array $attachments = [];

    // Modal Verifikasi Admin
    public bool $showVerifyModal = false;
    public ?string $verifyingContributionId = null;
    public string $verifyStatus = 'approved';
    public string $reviewer_notes = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatingFilterCategory(): void
    {
        $this->resetPage();
    }

    public function updatingFilterDivision(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->authorize('create', Contribution::class);
        $this->resetForm();
        $this->showFormModal = true;
    }

    public function openEditModal(string $id): void
    {
        $contribution = Contribution::findOrFail($id);
        $this->authorize('update', $contribution);

        $this->editingContributionId = $contribution->id;
        $this->event_id = $contribution->event_id;
        $this->category = $contribution->category;
        $this->work_description = $contribution->work_description;
        $this->work_output = $contribution->work_output;
        $this->obstacles = $contribution->obstacles ?? '';
        $this->attachments = [];
        $this->showFormModal = true;
    }

    public function saveContribution(ContributionService $service): void
    {
        $validated = $this->validate([
            'event_id' => ['required', 'exists:events,id'],
            'category' => ['required', Rule::in(Contribution::CATEGORIES)],
            'work_description' => ['required', 'string', 'min:10', 'max:3000'],
            'work_output' => ['required', 'string', 'min:5', 'max:2000'],
            'obstacles' => ['nullable', 'string', 'max:1500'],
            'attachments.*' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,mp4,pdf', 'max:51200'],
        ]);

        if ($this->editingContributionId) {
            $contribution = Contribution::with('member')->findOrFail($this->editingContributionId);
            $this->authorize('update', $contribution);
            $service->updateContribution($contribution, $validated, $this->attachments);
            session()->flash('success', 'Laporan kontribusi berhasil diperbarui dan diajukan ulang.');
        } else {
            $this->authorize('create', Contribution::class);
            $member = Auth::user()->member;
            $service->submitContribution($member, $validated, $this->attachments);
            session()->flash('success', 'Kontribusi berhasil dikirim dan menunggu verifikasi pengurus.');
        }

        $this->showFormModal = false;
        $this->resetForm();
    }

    public function openVerifyModal(string $id): void
    {
        $contribution = Contribution::findOrFail($id);
        $this->authorize('verify', $contribution);

        $this->verifyingContributionId = $contribution->id;
        $this->verifyStatus = $contribution->status === 'pending' ? 'approved' : $contribution->status;
        $this->reviewer_notes = $contribution->reviewer_notes ?? '';
        $this->showVerifyModal = true;
    }

    public function submitVerification(ContributionService $service): void
    {
        $contribution = Contribution::with('member')->findOrFail($this->verifyingContributionId);
        $this->authorize('verify', $contribution);

        $validated = $this->validate([
            'verifyStatus' => ['required', Rule::in(['approved', 'rejected', 'revision_required'])],
            'reviewer_notes' => [
                Rule::requiredIf(in_array($this->verifyStatus, ['rejected', 'revision_required'], true)),
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        $service->verifyContribution($contribution, Auth::user(), $validated['verifyStatus'], $validated['reviewer_notes']);

        $this->showVerifyModal = false;
        $this->reset(['verifyingContributionId', 'reviewer_notes']);
        session()->flash('success', 'Status verifikasi kontribusi berhasil diperbarui.');
    }

    private function resetForm(): void
    {
        $this->reset(['editingContributionId', 'event_id', 'work_description', 'work_output', 'obstacles', 'attachments']);
        $this->category = 'Software Development';
        $this->resetValidation();
    }

    public function render()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $isAdmin = $user->hasAnyRole(['super_admin', 'admin']);

        $contributions = Contribution::query()
            ->with(['event', 'member.division', 'member.position', 'documentations', 'verifier'])
            ->when(! $isAdmin, fn($q) => $q->where('member_id', $user->member?->id))
            ->when($this->search, function ($q) {
                $term = '%' . $this->search . '%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('work_description', 'like', $term)
                        ->orWhere('work_output', 'like', $term)
                        ->orWhereHas('member', fn($m) => $m->where('full_name', 'like', $term)->orWhere('nim', 'like', $term));
                });
            })
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterCategory, fn($q) => $q->where('category', $this->filterCategory))
            ->when($this->filterDivision, fn($q) => $q->whereHas('member', fn($m) => $m->where('division_id', $this->filterDivision)))
            ->latest('submitted_at')
            ->paginate(10);

        $events = Event::whereIn('status', ['ongoing', 'completed', 'upcoming'])->orderByDesc('event_date')->get();
        $divisions = Division::where('is_active', true)->orderBy('name')->get();
        $currentMember = $user->member?->load(['division', 'position']);

        return view('livewire.contributions.contribution-index', compact(
            'contributions',
            'events',
            'divisions',
            'isAdmin',
            'currentMember'
        ));
    }
}
