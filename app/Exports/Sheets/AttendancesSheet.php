<?php

namespace App\Exports\Sheets;

use App\Models\Attendance;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AttendancesSheet implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(
        private readonly ?string $dateFrom = null,
        private readonly ?string $dateTo = null,
    ) {}

    public function title(): string
    {
        return 'Log_Absensi';
    }

    public function query(): Builder
    {
        return Attendance::query()
            ->with(['event', 'member.division', 'member.position'])
            ->when($this->dateFrom, fn (Builder $q) => $q->whereDate('checked_in_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn (Builder $q) => $q->whereDate('checked_in_at', '<=', $this->dateTo))
            ->latest('checked_in_at');
    }

    public function headings(): array
    {
        return ['Kode Kegiatan', 'Nama Kegiatan', 'NIM', 'Nama Anggota', 'Divisi', 'Jabatan', 'Waktu Check-In (WIB)', 'Metode', 'Status'];
    }

    /** @param Attendance $att */
    public function map($att): array
    {
        return [
            $att->event->event_code,
            $att->event->title,
            $att->member->nim,
            $att->member->full_name,
            $att->member->division?->name ?? '-',
            $att->member->position?->name ?? 'Anggota',
            $att->checked_in_at->timezone('Asia/Jakarta')->format('Y-m-d H:i:s'),
            strtoupper($att->check_in_method),
            strtoupper($att->status),
        ];
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
