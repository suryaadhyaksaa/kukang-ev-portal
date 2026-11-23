<?php

namespace App\Exports;

use App\Exports\Sheets\AttendancesSheet;
use App\Exports\Sheets\ContributionsSheet;
use App\Exports\Sheets\DivisionSummarySheet;
use App\Exports\Sheets\EventsSheet;
use App\Exports\Sheets\ExecutiveSummarySheet;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class FullReportWorkbookExport implements Export, WithMultipleSheets
{
    use Exportable;

    public function __construct(
        private readonly ?string $dateFrom = null,
        private readonly ?string $dateTo = null,
    ) {}

    public function sheets(): array
    {
        return [
            new ExecutiveSummarySheet,
            new MembersExport,
            new EventsSheet($this->dateFrom,$this->dateTo),
            new AttendancesSheet($this->dateFrom,$this->dateTo),
            new ContributionsSheet($this->dateFrom,$this->dateTo),
            new DivisionSummarySheet,
        ];
    }
}
