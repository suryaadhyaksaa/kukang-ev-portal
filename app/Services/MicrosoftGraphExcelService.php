<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Attendance;
use App\Models\Contribution;
use App\Models\MsGraphSyncLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Throwable;

class MicrosoftGraphExcelService
{
    public function isConfigured(): bool
    {
        $cfg = config('services.msgraph');

        return ! empty($cfg['tenant_id'])
            && ! empty($cfg['client_id'])
            && ! empty($cfg['client_secret'])
            && ! empty($cfg['drive_id'])
            && ! empty($cfg['workbook_item_id']);
    }

    public function getAccessToken(): string
    {
        $cfg = config('services.msgraph');

        if (! $this->isConfigured()) {
            throw new RuntimeException('Kredensial Microsoft Graph API (MS_GRAPH_*) belum dikonfigurasi pada file .env.');
        }

        $response = Http::asForm()
            ->timeout(20)
            ->post("https://login.microsoftonline.com/{$cfg['tenant_id']}/oauth2/v2.0/token", [
                'client_id' => $cfg['client_id'],
                'client_secret' => $cfg['client_secret'],
                'scope' => 'https://graph.microsoft.com/.default',
                'grant_type' => 'client_credentials',
            ]);

        if (! $response->successful() || empty($response->json('access_token'))) {
            throw new RuntimeException('Gagal memperoleh OAuth2 token Microsoft Graph: ' . $response->body());
        }

        return (string) $response->json('access_token');
    }

    public function syncWorkbook(?string $triggeredByUserId = null): MsGraphSyncLog
    {
        $cfg = config('services.msgraph');
        $log = $this->createSyncLog('full_workbook', 'processing', $triggeredByUserId, $cfg['workbook_item_id'] ?? null);

        try {
            $token = $this->getAccessToken();
            $baseUrl = "https://graph.microsoft.com/v1.0/drives/{$cfg['drive_id']}/items/{$cfg['workbook_item_id']}/workbook";

            $attendanceRows = Attendance::with(['event', 'member.division'])
                ->latest('checked_in_at')
                ->limit(100)
                ->get()
                ->map(function ($a) {
                    /** @var Attendance $a */
                    return [
                        $a->event->event_code,
                        $a->member->nim,
                        $a->member->full_name,
                        $a->member->division?->name ?? '-',
                        $a->checked_in_at->timezone('Asia/Jakarta')->format('Y-m-d H:i:s'),
                        strtoupper($a->status),
                    ];
                })
                ->all();

            $contributionRows = Contribution::with(['event', 'member.division'])
                ->latest('submitted_at')
                ->limit(100)
                ->get()
                ->map(function ($c) {
                    /** @var Contribution $c */
                    return [
                        $c->submitted_at->timezone('Asia/Jakarta')->format('Y-m-d H:i'),
                        $c->member->nim,
                        $c->member->full_name,
                        $c->event->title,
                        $c->category,
                        $c->work_output,
                        strtoupper($c->status),
                    ];
                })
                ->all();

            if (! empty($attendanceRows)) {
                $resAtt = Http::withToken($token)
                    ->timeout(25)
                    ->post("{$baseUrl}/worksheets('Log_Absensi')/tables('TblAbsensi')/rows/add", [
                        'values' => $attendanceRows,
                    ]);

                if (! $resAtt->successful()) {
                    throw new RuntimeException('Gagal sinkronisasi sheet Log_Absensi: ' . $resAtt->body());
                }
            }

            if (! empty($contributionRows)) {
                $resCont = Http::withToken($token)
                    ->timeout(25)
                    ->post("{$baseUrl}/worksheets('Log_Kontribusi')/tables('TblKontribusi')/rows/add", [
                        'values' => $contributionRows,
                    ]);

                if (! $resCont->successful()) {
                    throw new RuntimeException('Gagal sinkronisasi sheet Log_Kontribusi: ' . $resCont->body());
                }
            }

            $totalSynced = count($attendanceRows) + count($contributionRows);
            $this->finalizeSyncLog($log, 'success', $totalSynced, null);

            ActivityLog::record(
                'msgraph.synced',
                "Sinkronisasi Microsoft Graph Excel Online berhasil ({$totalSynced} baris).",
                MsGraphSyncLog::class,
                $log->id
            );

            return $log->fresh();
        } catch (Throwable $e) {
            $this->finalizeSyncLog($log, 'failed', 0, $e->getMessage());

            ActivityLog::record(
                'msgraph.failed',
                'Sinkronisasi Microsoft Graph Excel Online gagal: ' . $e->getMessage(),
                MsGraphSyncLog::class,
                $log->id
            );

            return $log->fresh();
        }
    }

    private function createSyncLog(string $syncType, string $status, ?string $userId, ?string $workbookId): MsGraphSyncLog
    {
        $columns = Schema::getColumnListing('ms_graph_sync_logs');
        $payload = [];

        if (in_array('sync_type', $columns, true)) {
            $payload['sync_type'] = $syncType;
        }
        if (in_array('status', $columns, true)) {
            $payload['status'] = $status;
        }
        if (in_array('triggered_by', $columns, true)) {
            $payload['triggered_by'] = $userId;
        }
        if (in_array('workbook_id', $columns, true)) {
            $payload['workbook_id'] = $workbookId;
        }
        if (in_array('started_at', $columns, true)) {
            $payload['started_at'] = now();
        }

        return MsGraphSyncLog::create($payload);
    }

    private function finalizeSyncLog(MsGraphSyncLog $log, string $status, int $recordsCount, ?string $errorMessage): void
    {
        $columns = Schema::getColumnListing('ms_graph_sync_logs');
        $updates = [];

        if (in_array('status', $columns, true)) {
            $updates['status'] = $status;
        }
        if (in_array('records_synced', $columns, true)) {
            $updates['records_synced'] = $recordsCount;
        }
        if (in_array('records_count', $columns, true)) {
            $updates['records_count'] = $recordsCount;
        }
        if (in_array('error_message', $columns, true)) {
            $updates['error_message'] = $errorMessage;
        }
        if (in_array('completed_at', $columns, true)) {
            $updates['completed_at'] = now();
        }
        if (in_array('synced_at', $columns, true)) {
            $updates['synced_at'] = now();
        }

        $log->update($updates);
    }
}
