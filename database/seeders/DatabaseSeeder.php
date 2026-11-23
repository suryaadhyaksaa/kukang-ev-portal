<?php

namespace Database\Seeders;

use App\Models\Division;
use App\Models\Member;
use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'admins.manage',
            'system.settings',
            'logs.view',
            'excel.sync',
            'members.view',
            'members.manage',
            'events.view',
            'events.manage',
            'qr.generate',
            'attendance.scan',
            'attendance.view_all',
            'contributions.submit',
            'contributions.verify',
            'documentation.upload',
            'documentation.manage',
            'reports.export',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions($permissions);

        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions([
            'members.view',
            'members.manage',
            'events.view',
            'events.manage',
            'qr.generate',
            'attendance.scan',
            'attendance.view_all',
            'contributions.submit',
            'contributions.verify',
            'documentation.upload',
            'documentation.manage',
            'reports.export',
        ]);

        $anggota = Role::firstOrCreate(['name' => 'anggota', 'guard_name' => 'web']);
        $anggota->syncPermissions([
            'events.view',
            'attendance.scan',
            'contributions.submit',
            'documentation.upload',
        ]);

        // Divisi & Jabatan Awal KUKANG EV ITERA
        $tech = Division::firstOrCreate(
            ['code' => 'TECH'],
            ['name' => 'Technical Team', 'description' => 'Pengembangan powertrain, baterai, kelistrikan, sasis, dan sistem kontrol EV.']
        );
        $nonTech = Division::firstOrCreate(
            ['code' => 'NONTECH'],
            ['name' => 'Non-Technical Team', 'description' => 'Sponsorship, branding, publikasi, dan manajemen operasional tim.']
        );
        $principals = Division::firstOrCreate(
            ['code' => 'ADMIN-PRINC'],
            ['name' => 'Administrasi & Principals', 'description' => 'Pimpinan tim, sekretariat, dan tata kelola organisasi KUKANG EV ITERA.']
        );

        $teamLeaderPos = Position::firstOrCreate(
            ['division_id' => $principals->id, 'name' => 'Team Principal'],
            ['hierarchy_level' => 1]
        );
        Position::firstOrCreate(['division_id' => $tech->id, 'name' => 'Technical Lead'], ['hierarchy_level' => 2]);
        Position::firstOrCreate(['division_id' => $nonTech->id, 'name' => 'Manager Non-Technical'], ['hierarchy_level' => 2]);

        // Akun Super Admin Awal
        $adminUser = User::firstOrCreate(
            ['email' => 'superadmin@kukangev.itera.ac.id'],
            [
                'name' => 'Super Admin KUKANG EV',
                'password' => Hash::make('KukangEV#2026!'),
                'is_active' => true,
            ]
        );
        $adminUser->assignRole($superAdmin);

        Member::firstOrCreate(
            ['user_id' => $adminUser->id],
            [
                'division_id' => $principals->id,
                'position_id' => $teamLeaderPos->id,
                'nim' => '121140001',
                'full_name' => 'Super Admin KUKANG EV',
                'email' => 'superadmin@kukangev.itera.ac.id',
                'phone' => '081200000001',
                'cohort_year' => 2021,
                'joined_at' => '2024-01-10',
                'status' => 'active',
            ]
        );
    }
}
