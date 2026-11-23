<?php

namespace App\Livewire\Reports;

use App\Exports\FullReportWorkbookExport;
use App\Jobs\SyncReportToMicrosoftGraphJob;
use App\Models\MsGraphSyncLog;
use App\Services\MicrosoftGraphExcelService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

#[Layout('layouts.app')]
#[Title('Laporan Excel & Sinkronisasi Cloud')]
class ReportSyncCenter extends Component
{
    public ?string $dateFrom = null;

    public ?string $dateTo = null;

    public function downloadReport(): BinaryFileResponse
    {
        $filename = 'KUKANG-EV-Report-'.now()->timezone('Asia/Jakarta')->format('Ymd-His').'.xlsx';

        return Excel::download(
            new FullReportWorkbookExport($this->dateFrom ?: null, $this->dateTo ?: null),
            $filename
        );
    }

    public function triggerCloudSync(MicrosoftGraphExcelService $graphService): void
    {
        if (! $graphService->isConfigured()) {
            session()->flash('error', 'Konfigurasi Microsoft Graph API belum lengkap di file .env.');

            return;
        }

        SyncReportToMicrosoftGraphJob::dispatch('full_sync', Auth::id());

        session()->flash('status', 'Tugas sinkronisasi Microsoft Graph berhasil dimasukkan ke antrean (Queue).');
    }

    public function render(MicrosoftGraphExcelService $graphService)
    {
        return view('livewire.reports.report-sync-center', [
            'isGraphConfigured' => $graphService->isConfigured(),
            'syncLogs' => MsGraphSyncLog::with('triggeredBy')
                ->latest('created_at')
                ->limit(10)
                ->get(),
        ]);
    }
}
