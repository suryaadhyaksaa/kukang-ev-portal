<?php

namespace Tests\Feature;

use App\Livewire\Attendance\QrScanner;
use App\Livewire\Contributions\ContributionIndex;
use App\Models\Contribution;
use App\Models\Event;
use App\Models\Member;
use App\Models\User;
use App\Services\QRCodeService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class PhaseTenSecurityAndAuditTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_deactivated_member_cannot_login(): void
    {
        $user = User::create([
            'name' => 'Nonaktif User',
            'email' => 'nonaktif@student.itera.ac.id',
            'password' => Hash::make('Password123'),
            'is_active' => false,
        ]);
        $user->assignRole('anggota');

        $this->post('/login', [
            'email' => 'nonaktif@student.itera.ac.id',
            'password' => 'Password123',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_regular_member_cannot_access_admin_reports_or_divisions(): void
    {
        $user = User::create([
            'name' => 'Regular Member',
            'email' => 'regular@student.itera.ac.id',
            'password' => Hash::make('Password123'),
            'is_active' => true,
        ]);
        $user->assignRole('anggota');

        $this->actingAs($user)->get(route('reports.index'))->assertForbidden();
        $this->actingAs($user)->get(route('divisions.index'))->assertForbidden();
    }

    public function test_complete_end_to_end_operational_workflow(): void
    {
        /** @var User $admin */
        $admin = User::where('email', 'superadmin@kukangev.itera.ac.id')->firstOrFail();

        $memberUser = User::create([
            'name' => 'Fajar Pratama',
            'email' => 'fajar@student.itera.ac.id',
            'password' => Hash::make('Password123'),
            'is_active' => true,
        ]);
        $memberUser->assignRole('anggota');

        $member = Member::create([
            'user_id' => $memberUser->id,
            'nim' => '121140088',
            'full_name' => 'Fajar Pratama',
            'email' => 'fajar@student.itera.ac.id',
            'cohort_year' => 2021,
            'joined_at' => now()->toDateString(),
            'status' => 'active',
        ]);

        // 1. Admin membuat kegiatan & QR Code
        $event = Event::create([
            'event_code' => 'EVT-E2E-2026',
            'title' => 'Uji Jalan Prototipe KUKANG EV Gen-3',
            'event_date' => now()->toDateString(),
            'start_time' => now()->format('H:i:s'),
            'end_time' => now()->addHours(3)->format('H:i:s'),
            'location' => 'Sirkuit Uji ITERA',
            'status' => 'ongoing',
            'created_by' => $admin->id,
        ]);
        $event->participants()->sync([$member->id]);

        $qrData = app(QRCodeService::class)->generateForEvent($event, 90, $admin->id);

        // 2. Anggota scan QR & check-in
        Livewire::actingAs($memberUser)
            ->test(QrScanner::class)
            ->call('verifyScannedToken', $qrData['rawToken'], 'qr_scan')
            ->call('confirmCheckIn')
            ->assertSet('errorMessage', null);

        // 3. Anggota mengirim laporan kontribusi
        Livewire::actingAs($memberUser)
            ->test(ContributionIndex::class)
            ->set('event_id', $event->id)
            ->set('category', 'Mechanical Development')
            ->set('work_description', 'Kalibrasi sistem pengereman regeneratif dan suspensi depan.')
            ->set('work_output', 'Jarak pengereman optimal tercapai pada kecepatan 50 km/jam.')
            ->call('saveContribution')
            ->assertHasNoErrors();

        $contribution = Contribution::where('member_id', $member->id)->firstOrFail();

        // 4. Admin memverifikasi kontribusi
        Livewire::actingAs($admin)
            ->test(ContributionIndex::class)
            ->call('openVerifyModal', $contribution->id)
            ->set('verifyStatus', 'approved')
            ->set('reviewer_notes', 'Data telemetri rem tervalidasi.')
            ->call('submitVerification')
            ->assertHasNoErrors();

        // 5. Anggota melihat profil & statistik 100% kehadiran
        $this->actingAs($memberUser)
            ->get(route('profile.show'))
            ->assertOk()
            ->assertSee('100%')
            ->assertSee('Fajar Pratama');
    }
}
