<?php

namespace App\Jobs;

use App\Services\MicrosoftGraphExcelService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncReportToMicrosoftGraphJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $backoff = 10;

    public function __construct(public readonly ?string $triggeredByUserId = null) {}

    public function handle(MicrosoftGraphExcelService $service): void
    {
        $service->syncWorkbook($this->triggeredByUserId);
    }
}
