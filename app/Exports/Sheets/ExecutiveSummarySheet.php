<?php

namespace App\Exports\Sheets;

use App\Models\Attendance;
use App\Models\Contribution;
use App\Models\Division;
use App\Models\Documentation;
use App\Models\Event;
use App\Models\Member;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExecutiveSummarySheet implements FromArray, ShouldAutoSize, WithStyles, WithTitle
{
    public function title(): string
    {
        return 'Ringkasan_Eksekutif';
    }

    public function array(): array
    {
        return [
            ['LAPORAN EKSEKUTIF KUKANG EV ITERA — ATTENDANCE & CONTRIBUTION TRACKER'],
            ['Tanggal Ekspor (WIB)', now()->timezone('Asia/Jakarta')->format('d F Y H:i:s')],
            [''],
            ['INDIKATOR UTAMA ORGANISASI', 'NILAI AKTUAL'],
            ['Total Anggota Aktif', Member::where('status', 'active')->count()],
            ['Total Anggota Alumni / Nonaktif', Member::where('status', '!=', 'active')->count()],
            ['Total Divisi Aktif', Division::where('is_active', true)->count()],
            ['Total Kegiatan Terdaftar', Event::count()],
            ['Total Record Kehadiran (Check-In)', Attendance::count()],
            ['Total Laporan Kontribusi', Contribution::count()],
            ['Kontribusi Status Approved', Contribution::where('status', 'approved')->count()],
            ['Kontribusi Status Pending / Revisi', Contribution::whereIn('status', ['pending', 'revision_required'])->count()],
            ['Total File Dokumentasi Tersimpan', Documentation::count()],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 13]],
            4 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'F8FAFC']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '0F172A']],
            ],
        ];
    }
}
