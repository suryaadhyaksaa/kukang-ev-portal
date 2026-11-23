<?php

namespace App\Livewire\Documentation;

use App\Models\Division;
use App\Models\Documentation;
use App\Models\Event;
use App\Models\Member;
use App\Services\DocumentationService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Galeri Dokumentasi')]
class DocumentationGallery extends Component
{
    use AuthorizesRequests, WithFileUploads, WithPagination;

    public string $filterEvent = '';
    public string $filterMember = '';
    public string $filterDivision = '';
    public string $filterType = '';

    // Modal Upload
    public bool $showUploadModal = false;
    public string $title = '';
    public string $event_id = '';
    public string $visibility = 'internal_team';

    /** @var array<int, mixed> */
    public array $files = [];

    // Modal Preview
    public ?Documentation $previewDoc = null;

    public function updatingFilterEvent(): void
    {
        $this->resetPage();
    }

    public function updatingFilterMember(): void
    {
        $this->resetPage();
    }

    public function updatingFilterDivision(): void
    {
        $this->resetPage();
    }

    public function updatingFilterType(): void
    {
        $this->resetPage();
    }

    public function uploadFiles(DocumentationService $service): void
    {
        $this->authorize('create', Documentation::class);

        $validated = $this->validate([
            'title' => ['required', 'string', 'max:200'],
            'event_id' => ['required', 'exists:events,id'],
            'visibility' => ['required', Rule::in(['internal_team', 'private_member'])],
            'files' => ['required', 'array', 'min:1', 'max:10'],
            'files.*' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,mp4,pdf', 'max:51200'],
        ]);

        $member = Auth::user()->member;
        $service->uploadMultiple(
            $this->files,
            $member,
            $validated['event_id'],
            $validated['title'],
            null,
            $validated['visibility']
        );

        $this->reset(['showUploadModal', 'title', 'event_id', 'files']);
        $this->visibility = 'internal_team';
        session()->flash('success', 'Dokumentasi berhasil diunggah ke penyimpanan terproteksi.');
    }

    public function openPreview(string $docId): void
    {
        $doc = Documentation::with(['event', 'uploader'])->findOrFail($docId);
        $this->authorize('view', $doc);
        $this->previewDoc = $doc;
    }

    public function closePreview(): void
    {
        $this->previewDoc = null;
    }

    public function deleteDoc(string $docId, DocumentationService $service): void
    {
        $doc = Documentation::findOrFail($docId);
        $this->authorize('delete', $doc);

        $service->deleteDocumentation($doc);
        if ($this->previewDoc?->id === $docId) {
            $this->previewDoc = null;
        }
        session()->flash('success', 'File dokumentasi berhasil dihapus.');
    }

    public function render()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $isAdmin = $user->hasAnyRole(['super_admin', 'admin']);
        $documentations = Documentation::query()
            ->with(['event', 'uploader.division', 'contribution'])
            ->when(! $isAdmin, function ($q) use ($user) {
                $q->where(function ($sub) use ($user) {
                    $sub->where('visibility', 'internal_team')
                        ->orWhere('uploaded_by_member_id', $user->member?->id);
                });
            })
            ->when($this->filterEvent, fn($q) => $q->where('event_id', $this->filterEvent))
            ->when($this->filterMember, fn($q) => $q->where('uploaded_by_member_id', $this->filterMember))
            ->when($this->filterDivision, fn($q) => $q->whereHas('uploader', fn($m) => $m->where('division_id', $this->filterDivision)))
            ->when($this->filterType === 'image', fn($q) => $q->where('mime_type', 'like', 'image/%'))
            ->when($this->filterType === 'video', fn($q) => $q->where('mime_type', 'like', 'video/%'))
            ->when($this->filterType === 'pdf', fn($q) => $q->where('mime_type', 'application/pdf'))
            ->latest()
            ->paginate(12);

        $events = Event::orderByDesc('event_date')->get();
        $members = Member::where('status', 'active')->orderBy('full_name')->get();
        $divisions = Division::where('is_active', true)->orderBy('name')->get();

        return view('livewire.documentation.documentation-gallery', compact(
            'documentations',
            'events',
            'members',
            'divisions'
        ));
    }
}
