<?php

namespace App\Exports\Sheets;

use App\Models\Contribution;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ContributionsSheet implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(
        private readonly ?string $dateFrom = null,
        private readonly ?string $dateTo = null,
    ) {}

    public function title(): string
    {
        return 'Log_Kontribusi';
    }

    public function query(): Builder
    {
        return Contribution::query()
            ->with(['event', 'member.division', 'verifier'])
            ->when($this->dateFrom, fn (Builder $q) => $q->whereDate('submitted_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn (Builder $q) => $q->whereDate('submitted_at', '<=', $this->dateTo))
            ->latest('submitted_at');
    }

    public function headings(): array
    {
        return ['Tanggal Kirim (WIB)', 'NIM', 'Nama Anggota', 'Divisi', 'Kegiatan', 'Kategori', 'Deskripsi Pekerjaan', 'Output Pekerjaan', 'Status', 'Diverifikasi Oleh'];
    }

    /** @param Contribution $cont */
    public function map($cont): array
    {
        return [
            $cont->submitted_at->timezone('Asia/Jakarta')->format('Y-m-d H:i'),
            $cont->member->nim,
            $cont->member->full_name,
            $cont->member->division?->name ?? '-',
            $cont->event->title,
            $cont->category,
            $cont->work_description,
            $cont->work_output,
            strtoupper($cont->status),
            $cont->verifier?->name ?? '-',
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
