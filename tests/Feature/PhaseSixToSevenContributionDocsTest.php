<?php

namespace Tests\Feature;

use App\Livewire\Contributions\ContributionIndex;
use App\Livewire\Documentation\DocumentationGallery;
use App\Models\Contribution;
use App\Models\Documentation;
use App\Models\Event;
use App\Models\Member;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class PhaseSixToSevenContributionDocsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        Storage::fake('local');
    }

    private function createMemberUser(string $nim, string $email): array
    {
        $user = User::create([
            'name' => 'Member '.$nim,
            'email' => $email,
            'password' => Hash::make('Password123'),
            'is_active' => true,
        ]);
        $user->assignRole('anggota');

        $member = Member::create([
            'user_id' => $user->id,
            'nim' => $nim,
            'full_name' => 'Member '.$nim,
            'email' => $email,
            'cohort_year' => 2023,
            'joined_at' => now()->toDateString(),
            'status' => 'active',
        ]);

        return [$user, $member];
    }

    public function test_member_can_submit_contribution_and_admin_can_verify_but_member_cannot(): void
    {
        /** @var User $admin */
        $admin = User::where('email', 'superadmin@kukangev.itera.ac.id')->firstOrFail();
        [$memberUser, $member] = $this->createMemberUser('123140201', 'm201@student.itera.ac.id');

        $event = Event::create([
            'event_code' => 'EVT-CONT-01',
            'title' => 'Integrasi Sistem Telemetri EV',
            'event_date' => now()->toDateString(),
            'start_time' => '08:00:00',
            'end_time' => '12:00:00',
            'location' => 'Lab Elektro ITERA',
            'status' => 'ongoing',
            'created_by' => $admin->id,
        ]);

        Livewire::actingAs($memberUser)
            ->test(ContributionIndex::class)
            ->set('event_id', $event->id)
            ->set('category', 'Software Development')
            ->set('work_description', 'Membangun modul pembacaan data CAN Bus sensor suhu baterai.')
            ->set('work_output', 'Firmware pembacaan CAN Bus berjalan stabil pada frekuensi 10Hz.')
            ->call('saveContribution')
            ->assertHasNoErrors();

        $contribution = Contribution::where('member_id', $member->id)->firstOrFail();
        $this->assertEquals('pending', $contribution->status);

        // Anggota biasa tidak boleh memverifikasi kontribusi sendiri
        $this->assertFalse($memberUser->can('verify', $contribution));

        // Admin memverifikasi kontribusi menjadi approved
        Livewire::actingAs($admin)
            ->test(ContributionIndex::class)
            ->call('openVerifyModal', $contribution->id)
            ->set('verifyStatus', 'approved')
            ->set('reviewer_notes', 'Hasil pengujian sesuai target teknis.')
            ->call('submitVerification')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('contributions', [
            'id' => $contribution->id,
            'status' => 'approved',
            'verified_by' => $admin->id,
        ]);
    }

    public function test_private_documentation_cannot_be_accessed_by_other_regular_members(): void
    {
        /** @var User $admin */
        $admin = User::where('email', 'superadmin@kukangev.itera.ac.id')->firstOrFail();
        [$ownerUser] = $this->createMemberUser('123140202', 'm202@student.itera.ac.id');
        [$otherUser] = $this->createMemberUser('123140203', 'm203@student.itera.ac.id');

        $event = Event::create([
            'event_code' => 'EVT-DOC-01',
            'title' => 'Rapat Anggaran & Sponsorship',
            'event_date' => now()->toDateString(),
            'start_time' => '13:00:00',
            'end_time' => '15:00:00',
            'location' => 'Sekretariat KUKANG EV',
            'status' => 'ongoing',
            'created_by' => $admin->id,
        ]);

        $pdfFile = UploadedFile::fake()->create('proposal-rahasia.pdf', 512, 'application/pdf');

        Livewire::actingAs($ownerUser)
            ->test(DocumentationGallery::class)
            ->set('title', 'Dokumen Anggaran Rahasia')
            ->set('event_id', $event->id)
            ->set('visibility', 'private_member')
            ->set('files', [$pdfFile])
            ->call('uploadFiles')
            ->assertHasNoErrors();

        $doc = Documentation::firstOrFail();

        // Pemilik dan Admin boleh download
        $this->actingAs($ownerUser)->get(route('documentation.download', $doc))->assertOk();
        $this->actingAs($admin)->get(route('documentation.download', $doc))->assertOk();

        // Anggota lain ditolak (403 Forbidden)
        $this->actingAs($otherUser)->get(route('documentation.download', $doc))->assertForbidden();
    }
}
