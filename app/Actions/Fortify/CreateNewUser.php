<?php

namespace App\Actions\Fortify;

use App\Models\ActivityLog;
use App\Models\Member;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    public function create(array $input): User
    {
        Validator::make($input, [
            'nim' => ['required', 'string', 'max:20', Rule::unique(Member::class, 'nim')],
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'string', 'email', 'max:150', Rule::unique(User::class), Rule::unique(Member::class)],
            'phone' => ['nullable', 'string', 'max:25'],
            'cohort_year' => ['required', 'integer', 'min:2015', 'max:'.(now()->year + 1)],
            'password' => ['required', 'string', Password::min(8)->mixedCase()->numbers(), 'confirmed'],
        ])->validate();

        return DB::transaction(function () use ($input) {
            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => Hash::make($input['password']),
                'is_active' => true,
            ]);

            // Wajib hanya memberikan role 'anggota' pada registrasi publik
            $user->assignRole('anggota');

            $member = Member::create([
                'user_id' => $user->id,
                'nim' => trim($input['nim']),
                'full_name' => trim($input['name']),
                'email' => strtolower(trim($input['email'])),
                'phone' => $input['phone'] ?? null,
                'cohort_year' => (int) $input['cohort_year'],
                'joined_at' => now()->toDateString(),
                'status' => 'active',
            ]);

            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'member.registered',
                'description' => "Registrasi akun anggota baru: {$member->full_name} ({$member->nim})",
                'entity_type' => Member::class,
                'entity_id' => $member->id,
                'ip_address' => request()?->ip(),
                'created_at' => now(),
            ]);

            return $user;
        });
    }
}
