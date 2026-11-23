<?php

namespace App\Exports\Sheets;

use App\Models\Attendance;
use App\Models\Contribution;
use App\Models\Division;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DivisionSummarySheet implements FromArray, ShouldAutoSize, WithHeadings, WithStyles, WithTitle
{
    public function title(): string
    {
        return 'Rekap_Per_Divisi';
    }

    public function headings(): array
    {
        return ['Kode Divisi', 'Nama Divisi', 'Anggota Aktif', 'Total Check-In Anggota', 'Total Kontribusi', 'Kontribusi Approved'];
    }

    public function array(): array
    {
        return Division::where('is_active', true)
            ->withCount(['members' => fn ($q) => $q->where('status', 'active')])
            ->orderBy('name')
            ->get()
            ->map(function ($div) {
                /** @var Division $div */
                $attendances = Attendance::whereHas('member', fn ($q) => $q->where('division_id', $div->id))->count();
                $contributions = Contribution::whereHas('member', fn ($q) => $q->where('division_id', $div->id))->count();
                $approved = Contribution::where('status', 'approved')
                    ->whereHas('member', fn ($q) => $q->where('division_id', $div->id))
                    ->count();

                return [
                    $div->code,
                    $div->name,
                    $div->members_count,
                    $attendances,
                    $contributions,
                    $approved,
                ];
            })
            ->all();
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'F8FAFC']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '0F172A']],
            ],
        ];
    }
}
