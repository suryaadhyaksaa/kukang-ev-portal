<?php

namespace App\Livewire\Members;

use App\Exports\MembersExport;
use App\Imports\MembersImport;
use App\Models\ActivityLog;
use App\Models\Division;
use App\Models\Member;
use App\Models\Position;
use App\Services\MemberService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

#[Layout('layouts.app')]
#[Title('Manajemen Anggota')]
class MemberIndex extends Component
{
    use AuthorizesRequests, WithFileUploads, WithPagination;

    public string $search = '';
    public string $filterDivision = '';
    public string $filterStatus = '';
    public string $filterCohort = '';

    // Form Modal State
    public bool $showFormModal = false;
    public bool $showImportModal = false;
    public ?string $editingMemberId = null;

    public string $nim = '';
    public string $full_name = '';
    public string $email = '';
    public string $phone = '';
    public int $cohort_year = 2024;
    public string $division_id = '';
    public string $position_id = '';
    public string $joined_at = '';
    public string $status = 'active';
    public string $role = 'anggota';
    public string $password = '';
    public $avatar = null;
    public $importFile = null;

    /** @var array<int, string> */
    public array $importErrors = [];

    public function mount(): void
    {
        $this->authorize('viewAny', Member::class);
        $this->joined_at = now()->toDateString();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterDivision(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatingFilterCohort(): void
    {
        $this->resetPage();
    }

    public function updatedDivisionId(): void
    {
        $this->position_id = '';
    }

    public function openCreateModal(): void
    {
        $this->authorize('create', Member::class);
        $this->resetForm();
        $this->showFormModal = true;
    }

    public function openEditModal(string $memberId): void
    {
        $member = Member::with('user')->findOrFail($memberId);
        $this->authorize('update', $member);

        $this->editingMemberId = $member->id;
        $this->nim = $member->nim;
        $this->full_name = $member->full_name;
        $this->email = $member->email;
        $this->phone = $member->phone ?? '';
        $this->cohort_year = $member->cohort_year;
        $this->division_id = $member->division_id ?? '';
        $this->position_id = $member->position_id ?? '';
        $this->joined_at = $member->joined_at->format('Y-m-d');
        $this->status = $member->status;
        $this->role = $member->user?->getRoleNames()->first() ?? 'anggota';
        $this->password = '';
        $this->avatar = null;
        $this->showFormModal = true;
    }

    public function saveMember(MemberService $memberService): void
    {
        $editingMember = $this->editingMemberId ? Member::with('user')->findOrFail($this->editingMemberId) : null;

        if ($editingMember) {
            $this->authorize('update', $editingMember);
        } else {
            $this->authorize('create', Member::class);
        }

        $validated = $this->validate([
            'nim' => ['required', 'string', 'max:20', Rule::unique('members', 'nim')->ignore($editingMember?->id)],
            'full_name' => ['required', 'string', 'max:150'],
            'email' => [
                'required',
                'email',
                'max:150',
                Rule::unique('members', 'email')->ignore($editingMember?->id),
                Rule::unique('users', 'email')->ignore($editingMember?->user_id),
            ],
            'phone' => ['nullable', 'string', 'max:25'],
            'cohort_year' => ['required', 'integer', 'min:2015', 'max:'.(now()->year + 1)],
            'division_id' => ['nullable', 'exists:divisions,id'],
            'position_id' => ['nullable', 'exists:positions,id'],
            'joined_at' => ['required', 'date'],
            'status' => ['required', Rule::in(['active', 'inactive', 'alumni'])],
            'role' => ['required', Rule::in(['admin', 'anggota'])],
            'password' => [$editingMember ? 'nullable' : 'nullable', 'string', 'min:8'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($editingMember) {
            $memberService->updateMember($editingMember, $validated, $this->avatar);
            session()->flash('success', "Data anggota {$validated['full_name']} berhasil diperbarui.");
        } else {
            $memberService->createMember($validated, $this->avatar);
            session()->flash('success', "Anggota {$validated['full_name']} berhasil ditambahkan.");
        }

        $this->showFormModal = false;
        $this->resetForm();
    }

    public function deactivate(string $memberId, MemberService $memberService): void
    {
        $member = Member::with('user')->findOrFail($memberId);
        $this->authorize('deactivate', $member);

        $memberService->deactivateMember($member);
        session()->flash('success', "Anggota {$member->full_name} telah dinonaktifkan.");
    }

    public function exportExcel(): BinaryFileResponse
    {
        $this->authorize('viewAny', Member::class);

        ActivityLog::record('members.exported', 'Mengekspor data master anggota ke file Excel (.xlsx)');

        return Excel::download(
            new MembersExport($this->filterDivision ?: null, $this->filterStatus ?: null, $this->search ?: null),
            'KUKANG-EV-Anggota-'.now()->format('Ymd-His').'.xlsx'
        );
    }

    public function importExcel(MemberService $memberService): void
    {
        $this->authorize('create', Member::class);

        $this->validate([
            'importFile' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
        ]);

        $importer = new MembersImport($memberService);
        Excel::import($importer, $this->importFile->getRealPath());

        ActivityLog::record(
            'members.imported',
            "Mengimpor {$importer->importedCount} data anggota dari Excel."
        );

        $this->importErrors = $importer->errors;
        $this->importFile = null;

        if (empty($this->importErrors)) {
            $this->showImportModal = false;
            session()->flash('success', "Berhasil mengimpor {$importer->importedCount} anggota baru.");
        } else {
            session()->flash('warning', "Berhasil mengimpor {$importer->importedCount} anggota dengan beberapa baris dilewati.");
        }
    }

    private function resetForm(): void
    {
        $this->reset([
            'editingMemberId', 'nim', 'full_name', 'email', 'phone',
            'division_id', 'position_id', 'password', 'avatar',
        ]);
        $this->cohort_year = (int) now()->year;
        $this->joined_at = now()->toDateString();
        $this->status = 'active';
        $this->role = 'anggota';
        $this->resetValidation();
    }

    public function render()
    {
        $members = Member::query()
            ->with(['division', 'position', 'user.roles'])
            ->when($this->search, function ($query) {
                $term = '%'.$this->search.'%';
                $query->where(function ($sub) use ($term) {
                    $sub->where('nim', 'like', $term)
                        ->orWhere('full_name', 'like', $term)
                        ->orWhere('email', 'like', $term);
                });
            })
            ->when($this->filterDivision, fn ($q) => $q->where('division_id', $this->filterDivision))
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterCohort, fn ($q) => $q->where('cohort_year', $this->filterCohort))
            ->orderBy('nim')
            ->paginate(12);

        $divisions = Division::where('is_active', true)->orderBy('name')->get();
        $positions = $this->division_id
            ? Position::where('division_id', $this->division_id)->where('is_active', true)->orderBy('hierarchy_level')->get()
            : collect();
        $cohorts = Member::select('cohort_year')->distinct()->orderByDesc('cohort_year')->pluck('cohort_year');

        return view('livewire.members.member-index', compact('members', 'divisions', 'positions', 'cohorts'));
    }
}
