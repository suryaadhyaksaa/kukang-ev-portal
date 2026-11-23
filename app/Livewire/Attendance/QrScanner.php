<?php

namespace App\Livewire\Attendance;

use App\Models\Attendance;
use App\Services\AttendanceService;
use DomainException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Scan QR Absensi')]
class QrScanner extends Component
{
    public string $scannedCode = '';
    public string $scanMethod = 'qr_scan';

    // State layar konfirmasi sebelum menekan CHECK IN
    public bool $showConfirmation = false;
    public array $previewData = [];

    public ?string $errorMessage = null;
    public ?string $successMessage = null;

    public function verifyScannedToken(string $code, string $method, AttendanceService $attendanceService): void
    {
        $this->reset(['errorMessage', 'successMessage', 'showConfirmation', 'previewData']);

        $rateKey = 'qr-verify:'.Auth::id();
        if (RateLimiter::tooManyAttempts($rateKey, 15)) {
            $this->errorMessage = 'Terlalu banyak percobaan pemindaian. Tunggu beberapa detik.';
            return;
        }
        RateLimiter::hit($rateKey, 60);

        try {
            $user = Auth::user();
            $qrToken = $attendanceService->resolveValidToken($code, $user);
            $member = $user->member()->with(['division', 'position'])->firstOrFail();

            $this->scannedCode = trim($code);
            $this->scanMethod = $method;
            $this->previewData = [
                'nim' => $member->nim,
                'full_name' => $member->full_name,
                'division' => $member->division?->name ?? 'Umum',
                'position' => $member->position?->name ?? 'Anggota',
                'event_code' => $qrToken->event->event_code,
                'event_title' => $qrToken->event->title,
                'event_date' => $qrToken->event->event_date->translatedFormat('d F Y'),
                'location' => $qrToken->event->location,
            ];
            $this->showConfirmation = true;
        } catch (DomainException $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function confirmCheckIn(AttendanceService $attendanceService): void
    {
        $this->reset(['errorMessage', 'successMessage']);

        try {
            $attendance = $attendanceService->checkIn($this->scannedCode, Auth::user(), $this->scanMethod);
            $this->showConfirmation = false;
            $this->scannedCode = '';
            $this->successMessage = "Check-in berhasil pada pukul {$attendance->checked_in_at->timezone('Asia/Jakarta')->format('H:i:s')} WIB untuk kegiatan \"{$attendance->event->title}\".";
        } catch (DomainException $e) {
            $this->showConfirmation = false;
            $this->errorMessage = $e->getMessage();
        }
    }

    public function cancelConfirmation(): void
    {
        $this->reset(['scannedCode', 'showConfirmation', 'previewData', 'errorMessage']);
    }

    public function render()
    {
        $member = Auth::user()?->member;
        $recentAttendances = $member
            ? Attendance::with('event')
                ->where('member_id', $member->id)
                ->latest('checked_in_at')
                ->limit(8)
                ->get()
            : collect();

        return view('livewire.attendance.qr-scanner', compact('recentAttendances'));
    }
}
