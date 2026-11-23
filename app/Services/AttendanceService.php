<?php

namespace App\Services;

use App\Events\MemberCheckedIn;
use App\Models\ActivityLog;
use App\Models\Attendance;
use App\Models\EventQrToken;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    /**
     * Memvalidasi raw QR token ATAU manual_code tanpa menyimpan kehadiran terlebih dahulu
     * (untuk layar konfirmasi identitas & kegiatan sebelum tombol CHECK IN ditekan).
     */
    public function resolveValidToken(string $inputCode, User $user): EventQrToken
    {
        $cleanInput = trim($inputCode);

        if ($cleanInput === '') {
            throw new DomainException('Kode QR atau kode manual tidak boleh kosong.');
        }

        $member = $user->member;
        if (! $member || $member->status !== 'active') {
            throw new DomainException('Akun Anda tidak terhubung dengan profil anggota aktif KUKANG EV.');
        }

        $tokenHash = hash('sha256', $cleanInput);

        $qrToken = EventQrToken::with(['event.division', 'event.pic'])
            ->where(function ($q) use ($tokenHash, $cleanInput) {
                $q->where('token_hash', $tokenHash)
                    ->orWhere('manual_code', strtoupper($cleanInput));
            })
            ->first();

        if (! $qrToken) {
            throw new DomainException('Kode QR tidak dikenali atau tidak valid.');
        }

        if (! $qrToken->is_active) {
            throw new DomainException('QR Code ini telah dinonaktifkan oleh pengurus.');
        }

        if (now()->gt($qrToken->expires_at)) {
            throw new DomainException('Masa berlaku QR Code telah habis. Silakan minta pengurus membuat QR baru.');
        }

        $event = $qrToken->event;
        if (! $event || ! $event->isAttendanceWindowOpen()) {
            throw new DomainException('Sesi absensi untuk kegiatan ini sedang ditutup atau kegiatan dibatalkan.');
        }

        $alreadyCheckedIn = Attendance::where('event_id', $event->id)
            ->where('member_id', $member->id)
            ->exists();

        if ($alreadyCheckedIn) {
            throw new DomainException("Anda sudah tercatat hadir pada kegiatan \"{$event->title}\".");
        }

        return $qrToken;
    }

    /**
     * Menyimpan absensi dalam database transaction dengan timestamp murni dari server.
     */
    public function checkIn(string $inputCode, User $user, string $method = 'qr_scan'): Attendance
    {
        return DB::transaction(function () use ($inputCode, $user, $method) {
            $qrToken = $this->resolveValidToken($inputCode, $user);
            $member = $user->member;
            $event = $qrToken->event;

            // Lock baris pengecekan duplikasi untuk mencegah race condition double-tap
            $duplicate = Attendance::where('event_id', $event->id)
                ->where('member_id', $member->id)
                ->lockForUpdate()
                ->exists();

            if ($duplicate) {
                throw new DomainException('Absensi ganda terdeteksi: Anda sudah melakukan check-in pada kegiatan ini.');
            }

            $serverNow = now();
            $eventStart = $event->event_date->copy()->setTimeFromTimeString($event->start_time);
            $status = $serverNow->gt($eventStart->copy()->addMinutes(15)) ? 'late' : 'present';

            $attendance = Attendance::create([
                'event_id' => $event->id,
                'member_id' => $member->id,
                'qr_token_id' => $qrToken->id,
                'checked_in_at' => $serverNow,
                'status' => $status,
                'check_in_method' => $method,
                'ip_address' => request()?->ip(),
                'user_agent' => request()?->userAgent(),
            ]);

            ActivityLog::record(
                'attendance.checked_in',
                "{$member->full_name} ({$member->nim}) melakukan check-in pada kegiatan {$event->title}",
                Attendance::class,
                $attendance->id,
                ['method' => $method, 'status' => $status]
            );

            DB::afterCommit(function () use ($attendance) {
                event(new MemberCheckedIn($attendance));
            });

            return $attendance->load(['event', 'member.division', 'member.position']);
        });
    }
}
