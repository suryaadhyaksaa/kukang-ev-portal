<?php

namespace Tests\Feature;

use App\Livewire\Members\DivisionPositionManager;
use App\Livewire\Members\MemberIndex;
use App\Models\Division;
use App\Models\Member;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PhaseTwoMemberAndDivisionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_admin_can_create_and_deactivate_member_and_disable_user_login(): void
    {
        /** @var User $admin */
        $admin = User::where('email', 'superadmin@kukangev.itera.ac.id')->firstOrFail();
        $division = Division::where('code', 'TECH')->firstOrFail();

        Livewire::actingAs($admin)
            ->test(MemberIndex::class)
            ->set('nim', '123140055')
            ->set('full_name', 'Dimas Aditya')
            ->set('email', 'dimas.123140055@student.itera.ac.id')
            ->set('phone', '081234567891')
            ->set('cohort_year', 2023)
            ->set('division_id', $division->id)
            ->set('joined_at', '2026-01-15')
            ->set('status', 'active')
            ->set('role', 'anggota')
            ->call('saveMember')
            ->assertHasNoErrors();

        $member = Member::where('nim', '123140055')->firstOrFail();
        $this->assertTrue($member->user->is_active);

        Livewire::actingAs($admin)
            ->test(MemberIndex::class)
            ->call('deactivate', $member->id);

        $this->assertDatabaseHas('members', ['id' => $member->id, 'status' => 'inactive']);
        $this->assertDatabaseHas('users', ['id' => $member->user_id, 'is_active' => false]);
    }

    public function test_admin_can_create_dynamic_division_and_position(): void
    {
        /** @var User $admin */
        $admin = User::where('email', 'superadmin@kukangev.itera.ac.id')->firstOrFail();

        Livewire::actingAs($admin)
            ->test(DivisionPositionManager::class)
            ->set('code', 'CHASSIS')
            ->set('name', 'Chassis & Aerodynamics')
            ->set('description', 'Perancangan rangka ringan dan aerodinamika bodi EV.')
            ->call('saveDivision')
            ->assertHasNoErrors();

        $division = Division::where('code', 'CHASSIS')->firstOrFail();

        Livewire::actingAs($admin)
            ->test(DivisionPositionManager::class)
            ->set('selectedDivisionId', $division->id)
            ->set('positionName', 'Lead Frame Engineer')
            ->set('hierarchyLevel', 3)
            ->call('addPosition')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('positions', [
            'division_id' => $division->id,
            'name' => 'Lead Frame Engineer',
        ]);
    }

    public function test_admin_can_export_members_excel(): void
    {
        /** @var User $admin */
        $admin = User::where('email', 'superadmin@kukangev.itera.ac.id')->firstOrFail();

        Livewire::actingAs($admin)
            ->test(MemberIndex::class)
            ->call('exportExcel')
            ->assertFileDownloaded();
    }
}
