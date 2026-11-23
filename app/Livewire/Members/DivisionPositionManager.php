<?php

namespace App\Livewire\Members;

use App\Models\ActivityLog;
use App\Models\Division;
use App\Models\Position;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Manajemen Divisi & Jabatan')]
class DivisionPositionManager extends Component
{
    use AuthorizesRequests;

    public ?string $editingDivisionId = null;
    public string $code = '';
    public string $name = '';
    public string $description = '';

    public string $selectedDivisionId = '';
    public string $positionName = '';
    public int $hierarchyLevel = 10;

    public function mount(): void
    {
        $this->authorize('viewAny', Division::class);
    }

    public function saveDivision(): void
    {
        $this->authorize('manage', Division::class);

        $validated = $this->validate([
            'code' => ['required', 'string', 'max:30', Rule::unique('divisions', 'code')->ignore($this->editingDivisionId)],
            'name' => ['required', 'string', 'max:120', Rule::unique('divisions', 'name')->ignore($this->editingDivisionId)],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        if ($this->editingDivisionId) {
            $division = Division::findOrFail($this->editingDivisionId);
            $division->update([
                'code' => strtoupper(trim($validated['code'])),
                'name' => trim($validated['name']),
                'description' => $validated['description'],
            ]);
            ActivityLog::record('division.updated', "Memperbarui divisi: {$division->name}", Division::class, $division->id);
            session()->flash('success', 'Divisi berhasil diperbarui.');
        } else {
            $division = Division::create([
                'code' => strtoupper(trim($validated['code'])),
                'name' => trim($validated['name']),
                'description' => $validated['description'],
                'is_active' => true,
            ]);
            ActivityLog::record('division.created', "Menambahkan divisi baru: {$division->name}", Division::class, $division->id);
            session()->flash('success', 'Divisi baru berhasil ditambahkan.');
        }

        $this->reset(['editingDivisionId', 'code', 'name', 'description']);
    }

    public function editDivision(string $id): void
    {
        $this->authorize('manage', Division::class);
        $division = Division::findOrFail($id);
        $this->editingDivisionId = $division->id;
        $this->code = $division->code;
        $this->name = $division->name;
        $this->description = $division->description ?? '';
    }

    public function addPosition(): void
    {
        $this->authorize('manage', Division::class);

        $validated = $this->validate([
            'selectedDivisionId' => ['required', 'exists:divisions,id'],
            'positionName' => [
                'required',
                'string',
                'max:120',
                Rule::unique('positions', 'name')->where('division_id', $this->selectedDivisionId),
            ],
            'hierarchyLevel' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        $position = Position::create([
            'division_id' => $validated['selectedDivisionId'],
            'name' => trim($validated['positionName']),
            'hierarchy_level' => $validated['hierarchyLevel'],
            'is_active' => true,
        ]);

        ActivityLog::record('position.created', "Menambahkan jabatan: {$position->name}", Position::class, $position->id);
        $this->reset(['positionName']);
        $this->hierarchyLevel = 10;
        session()->flash('success', 'Jabatan berhasil ditambahkan.');
    }

    public function render()
    {
        $divisions = Division::with(['positions' => fn ($q) => $q->orderBy('hierarchy_level')])
            ->withCount('members')
            ->orderBy('name')
            ->get();

        return view('livewire.members.division-position-manager', compact('divisions'));
    }
}
