<?php

namespace App\Exports;

use App\Models\Member;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MembersExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(
        private readonly ?string $divisionId = null,
        private readonly ?string $status = null,
        private readonly ?string $search = null,
    ) {}

    public function query(): Builder
    {
        $query = Member::query()
            ->with(['division', 'position'])
            ->orderBy('nim');

        if ($this->divisionId) {
            $query->where('division_id', $this->divisionId);
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        if ($this->search) {
            $term = '%' . $this->search . '%';
            $query->where(function (Builder $sub) use ($term) {
                $sub->where('nim', 'like', $term)
                    ->orWhere('full_name', 'like', $term)
                    ->orWhere('email', 'like', $term);
            });
        }

        /** @var Builder $query */
        return $query;
    }

    public function title(): string
    {
        return 'Master_Anggota';
    }

    public function headings(): array
    {
        return [
            'NIM',
            'Nama Lengkap',
            'Email',
            'Nomor HP',
            'Angkatan',
            'Divisi',
            'Jabatan',
            'Tanggal Bergabung',
            'Status Keanggotaan',
        ];
    }

    /** @param Member $member */
    public function map($member): array
    {
        return [
            $member->nim,
            $member->full_name,
            $member->email,
            $member->phone ?? '-',
            $member->cohort_year,
            $member->division?->name ?? 'Belum Ditetapkan',
            $member->position?->name ?? 'Anggota',
            $member->joined_at ? Carbon::parse($member->joined_at)->format('Y-m-d') : '-',
            strtoupper($member->status),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'F8FAFC']],
                'fill' => [
                    'fillType' => 'solid',
                    'startColor' => ['rgb' => '0F172A'],
                ],
            ],
        ];
    }
}
