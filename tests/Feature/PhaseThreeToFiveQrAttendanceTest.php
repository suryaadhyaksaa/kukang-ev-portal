<?php

namespace Tests\Feature;

use App\Livewire\Attendance\QrScanner;
use App\Models\Attendance;
use App\Models\Event;
use App\Models\Member;
use App\Models\User;
use App\Services\QRCodeService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class PhaseThreeToFiveQrAttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_member_can_verify_qr_and_check_in_once_but_duplicate_is_blocked(): void
    {
        /** @var User $admin */
        $admin = User::where('email', 'superadmin@kukangev.itera.ac.id')->firstOrFail();

        $event = Event::create([
            'event_code' => 'EVT-TEST-001',
            'title' => 'Workshop Battery Management System',
            'event_date' => now()->toDateString(),
            'start_time' => now()->format('H:i:s'),
            'end_time' => now()->addHours(2)->format('H:i:s'),
            'location' => 'Workshop KUKANG EV',
            'status' => 'ongoing',
            'created_by' => $admin->id,
        ]);

        $qrData = app(QRCodeService::class)->generateForEvent($event, 60);

        // Token disimpan dalam bentuk SHA-256 hash, bukan plaintext
        $this->assertDatabaseHas('event_qr_tokens', [
            'event_id' => $event->id,
            'token_hash' => hash('sha256', $qrData['rawToken']),
            'is_active' => true,
        ]);

        // Buat akun anggota
        $memberUser = User::create([
            'name' => 'Aulia Rahman',
            'email' => 'aulia@student.itera.ac.id',
            'password' => Hash::make('Password123'),
            'is_active' => true,
        ]);
        $memberUser->assignRole('anggota');
        $member = Member::create([
            'user_id' => $memberUser->id,
            'nim' => '122140111',
            'full_name' => 'Aulia Rahman',
            'email' => 'aulia@student.itera.ac.id',
            'cohort_year' => 2022,
            'joined_at' => now()->toDateString(),
            'status' => 'active',
        ]);

        // 1. Verifikasi & Check-in pertama berhasil
        Livewire::actingAs($memberUser)
            ->test(QrScanner::class)
            ->call('verifyScannedToken', $qrData['rawToken'], 'qr_scan')
            ->assertSet('showConfirmation', true)
            ->call('confirmCheckIn')
            ->assertSet('showConfirmation', false)
            ->assertSet('errorMessage', null);

        $this->assertEquals(1, Attendance::where('event_id', $event->id)->where('member_id', $member->id)->count());

        // 2. Percobaan check-in kedua pada kegiatan sama ditolak
        Livewire::actingAs($memberUser)
            ->test(QrScanner::class)
            ->call('verifyScannedToken', $qrData['rawToken'], 'qr_scan')
            ->assertSet('showConfirmation', false)
            ->assertNotSet('errorMessage', null);
    }

    public function test_deactivated_qr_token_cannot_be_used_for_attendance(): void
    {
        /** @var User $admin */
        $admin = User::where('email', 'superadmin@kukangev.itera.ac.id')->firstOrFail();

        $event = Event::create([
            'event_code' => 'EVT-TEST-002',
            'title' => 'Briefing Komite Teknis',
            'event_date' => now()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '11:00:00',
            'location' => 'Aula ITERA',
            'status' => 'ongoing',
            'created_by' => $admin->id,
        ]);

        $qrService = app(QRCodeService::class);
        $qrData = $qrService->generateForEvent($event, 60);
        $qrService->deactivateToken($qrData['qrToken']);

        Livewire::actingAs($admin)
            ->test(QrScanner::class)
            ->call('verifyScannedToken', $qrData['rawToken'], 'qr_scan')
            ->assertSet('showConfirmation', false)
            ->assertNotSet('errorMessage', null);
    }
}
