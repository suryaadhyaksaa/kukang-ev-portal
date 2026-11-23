<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Member;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MemberService
{
    public function createMember(array $data, ?UploadedFile $avatar = null): Member
    {
        return DB::transaction(function () use ($data, $avatar) {
            $role = in_array($data['role'] ?? 'anggota', ['admin', 'anggota'], true)
                ? $data['role']
                : 'anggota';

            $user = User::create([
                'name' => trim($data['full_name']),
                'email' => strtolower(trim($data['email'])),
                'password' => Hash::make($data['password'] ?? 'KukangEV#'.$data['nim']),
                'is_active' => ($data['status'] ?? 'active') === 'active',
            ]);

            $user->assignRole($role);

            $avatarPath = $avatar ? $this->storeAvatar($avatar) : null;

            $member = Member::create([
                'user_id' => $user->id,
                'division_id' => $data['division_id'] ?: null,
                'position_id' => $data['position_id'] ?: null,
                'nim' => trim($data['nim']),
                'full_name' => trim($data['full_name']),
                'email' => strtolower(trim($data['email'])),
                'phone' => $data['phone'] ? trim($data['phone']) : null,
                'cohort_year' => (int) $data['cohort_year'],
                'avatar_path' => $avatarPath,
                'joined_at' => $data['joined_at'] ?? now()->toDateString(),
                'status' => $data['status'] ?? 'active',
            ]);

            ActivityLog::record(
                'member.created',
                "Menambahkan anggota baru: {$member->full_name} (NIM: {$member->nim})",
                Member::class,
                $member->id
            );

            return $member;
        });
    }

    public function updateMember(Member $member, array $data, ?UploadedFile $avatar = null): Member
    {
        return DB::transaction(function () use ($member, $data, $avatar) {
            $isActive = ($data['status'] ?? $member->status) === 'active';

            if ($avatar) {
                if ($member->avatar_path) {
                    Storage::disk('public')->delete($member->avatar_path);
                }
                $member->avatar_path = $this->storeAvatar($avatar);
            }

            $member->update([
                'division_id' => $data['division_id'] ?: null,
                'position_id' => $data['position_id'] ?: null,
                'nim' => trim($data['nim']),
                'full_name' => trim($data['full_name']),
                'email' => strtolower(trim($data['email'])),
                'phone' => $data['phone'] ? trim($data['phone']) : null,
                'cohort_year' => (int) $data['cohort_year'],
                'avatar_path' => $member->avatar_path,
                'joined_at' => $data['joined_at'],
                'status' => $data['status'],
            ]);

            if ($member->user) {
                $userPayload = [
                    'name' => $member->full_name,
                    'email' => $member->email,
                    'is_active' => $isActive,
                ];

                if (! empty($data['password'])) {
                    $userPayload['password'] = Hash::make($data['password']);
                }

                $member->user->update($userPayload);

                if (! empty($data['role']) && in_array($data['role'], ['admin', 'anggota'], true) && ! $member->user->hasRole('super_admin')) {
                    $member->user->syncRoles([$data['role']]);
                }
            }

            ActivityLog::record(
                'member.updated',
                "Memperbarui data anggota: {$member->full_name} (NIM: {$member->nim})",
                Member::class,
                $member->id
            );

            return $member->fresh(['division', 'position', 'user']);
        });
    }

    public function deactivateMember(Member $member): void
    {
        DB::transaction(function () use ($member) {
            $member->update(['status' => 'inactive']);

            if ($member->user) {
                $member->user->update(['is_active' => false]);
            }

            ActivityLog::record(
                'member.deactivated',
                "Menonaktifkan keanggotaan: {$member->full_name} (NIM: {$member->nim})",
                Member::class,
                $member->id
            );
        });
    }

    private function storeAvatar(UploadedFile $file): string
    {
        $filename = Str::ulid().'.'.$file->getClientOriginalExtension();

        return $file->storeAs('avatars', $filename, 'public');
    }
}
