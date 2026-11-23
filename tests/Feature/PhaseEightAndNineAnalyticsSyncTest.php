<?php

namespace Tests\Feature;

use App\Livewire\Reports\ReportSyncCenter;
use App\Models\User;
use App\Services\MicrosoftGraphExcelService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class PhaseEightAndNineAnalyticsSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_dashboard_renders_analytics_for_authenticated_admin(): void
    {
        /** @var User $admin */
        $admin = User::where('email', 'superadmin@kukangev.itera.ac.id')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Rata-Rata Kehadiran')
            ->assertSee('Distribusi per Divisi');
    }

    public function test_admin_can_download_full_six_sheet_excel_workbook(): void
    {
        /** @var User $admin */
        $admin = User::where('email', 'superadmin@kukangev.itera.ac.id')->firstOrFail();

        Livewire::actingAs($admin)
            ->test(ReportSyncCenter::class)
            ->call('downloadFullWorkbook')
            ->assertFileDownloaded();
    }

    public function test_microsoft_graph_excel_sync_service_authenticates_and_logs_success(): void
    {
        config()->set('services.msgraph', [
            'tenant_id' => 'test-tenant-id',
            'client_id' => 'test-client-id',
            'client_secret' => 'test-secret',
            'drive_id' => 'test-drive-id',
            'workbook_item_id' => 'test-workbook-id',
        ]);

        Http::fake([
            'https://login.microsoftonline.com/*' => Http::response([
                'access_token' => 'mock-graph-access-token',
                'token_type' => 'Bearer',
                'expires_in' => 3600,
            ], 200),
            'https://graph.microsoft.com/*' => Http::response([
                'index' => 0,
            ], 201),
        ]);

        /** @var User $admin */
        $admin = User::where('email', 'superadmin@kukangev.itera.ac.id')->firstOrFail();
        $log = app(MicrosoftGraphExcelService::class)->syncWorkbook($admin->id);

        $this->assertEquals('success', $log->status);
    }
}
