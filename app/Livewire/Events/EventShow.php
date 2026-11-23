<?php

namespace App\Livewire\Events;

use App\Models\Event;
use App\Models\EventQrToken;
use App\Services\QRCodeService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Layout('layouts.app')]
#[Title('Detail Kegiatan & QR Absensi')]
class EventShow extends Component
{
    use AuthorizesRequests;

    public Event $event;
    public int $validMinutes = 120;
    public ?string $activeRawToken = null;
    public ?string $activeManualCode = null;
    public ?string $qrSvgDataUri = null;
    public ?string $qrExpiresAt = null;

    public function mount(Event $event): void
    {
        $this->authorize('view', $event);
        $this->event = $event->load(['division', 'pic']);

        $existingToken = $this->event->qrTokens()
            ->where('is_active', true)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if ($existingToken) {
            $this->activeManualCode = $existingToken->manual_code;
            $this->qrExpiresAt = $existingToken->expires_at->timezone('Asia/Jakarta')->format('d M Y H:i').' WIB';
        }
    }

    public function generateQr(QRCodeService $qrService): void
    {
        $this->authorize('generateQr', $this->event);

        $this->validate([
            'validMinutes' => ['required', 'integer', 'min:5', 'max:720'],
        ]);

        $result = $qrService->generateForEvent($this->event, $this->validMinutes);

        $this->activeRawToken = $result['rawToken'];
        $this->activeManualCode = $result['qrToken']->manual_code;
        $this->qrSvgDataUri = $result['svgDataUri'];
        $this->qrExpiresAt = $result['qrToken']->expires_at->timezone('Asia/Jakarta')->format('d M Y H:i').' WIB';

        session()->flash('success', 'QR Code absensi baru berhasil dibuat dan siap dipindai anggota.');
    }

    public function deactivateActiveQr(QRCodeService $qrService): void
    {
        $this->authorize('generateQr', $this->event);

        $activeToken = $this->event->qrTokens()->where('is_active', true)->latest()->first();
        if ($activeToken) {
            $qrService->deactivateToken($activeToken);
        }

        $this->reset(['activeRawToken', 'activeManualCode', 'qrSvgDataUri', 'qrExpiresAt']);
        session()->flash('success', 'QR Code aktif telah dinonaktifkan.');
    }

    public function downloadQrPng(QRCodeService $qrService): ?StreamedResponse
    {
        $this->authorize('generateQr', $this->event);

        if (! $this->activeRawToken) {
            session()->flash('error', 'Silakan tekan Generate QR Code terlebih dahulu sebelum mengunduh PNG.');
            return null;
        }

        $pngBinary = $qrService->renderPngBinary($this->activeRawToken);
        $filename = 'QR-'.$this->event->event_code.'.png';

        return response()->streamDownload(function () use ($pngBinary) {
            echo $pngBinary;
        }, $filename, ['Content-Type' => 'image/png']);
    }

    #[On('echo-private:event.{event.id},.member.checked-in')]
    public function refreshAttendances(): void
    {
        $this->event->refresh();
    }

    public function render()
    {
        $attendances = $this->event->attendances()
            ->with(['member.division', 'member.position'])
            ->latest('checked_in_at')
            ->get();

        $participantsCount = $this->event->participants()->count();

        return view('livewire.events.event-show', compact('attendances', 'participantsCount'));
    }
}
