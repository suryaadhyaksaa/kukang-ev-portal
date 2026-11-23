<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhaseOneAuthAndRoleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_public_registration_creates_member_profile_with_anggota_role_only(): void
    {
        $response = $this->post('/register', [
            'nim' => '122140099',
            'name' => 'Raka Pratama',
            'email' => 'raka.122140099@student.itera.ac.id',
            'phone' => '081234567890',
            'cohort_year' => 2022,
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ]);

        $response->assertRedirect('/dashboard');

        $user = User::where('email', 'raka.122140099@student.itera.ac.id')->firstOrFail();
        $this->assertTrue($user->hasRole('anggota'));
        $this->assertFalse($user->hasRole('admin'));
        $this->assertFalse($user->hasRole('super_admin'));
        $this->assertDatabaseHas('members', [
            'user_id' => $user->id,
            'nim' => '122140099',
        ]);
    }

    public function test_anggota_cannot_access_admin_members_route(): void
    {
        /** @var User $user */
        $user = User::factory()->createOne(['is_active' => true]);
        $user->assignRole('anggota');

        $this->actingAs($user)
            ->get('/members')
            ->assertForbidden();
    }

    public function test_super_admin_can_access_admin_members_route(): void
    {
        $admin = User::where('email', 'superadmin@kukangev.itera.ac.id')->firstOrFail();

        $this->actingAs($admin)
            ->get('/members')
            ->assertOk();
    }
}
