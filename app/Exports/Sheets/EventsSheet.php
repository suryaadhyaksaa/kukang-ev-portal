<?php

namespace App\Exports\Sheets;

use App\Models\Event;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EventsSheet implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(
        private readonly ?string $dateFrom = null,
        private readonly ?string $dateTo = null,
    ) {}

    public function title(): string
    {
        return 'Daftar_Kegiatan';
    }

    public function query(): Builder
    {
        $query = Event::query()
            ->with(['division', 'pic'])
            ->withCount(['participants', 'attendances'])
            ->orderByDesc('event_date');

        if ($this->dateFrom) {
            $query->whereDate('event_date', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate('event_date', '<=', $this->dateTo);
        }

        return $query;
    }

    public function headings(): array
    {
        return ['Kode Kegiatan', 'Judul Kegiatan', 'Tanggal', 'Waktu (WIB)', 'Lokasi', 'Divisi', 'PIC', 'Target Peserta', 'Total Hadir', 'Status'];
    }

    /** @param Event $event */
    public function map($event): array
    {
        return [
            $event->event_code,
            $event->title,
            Carbon::parse($event->event_date)->format('Y-m-d'),
            substr($event->start_time, 0, 5).' - '.substr($event->end_time, 0, 5),
            $event->location,
            $event->division?->name ?? 'Seluruh Tim',
            $event->pic?->full_name ?? '-',
            $event->participants_count,
            $event->attendances_count,
            strtoupper($event->status),
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
