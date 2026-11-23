<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Event;
use App\Models\EventQrToken;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QRGdImagePNG;
use chillerlan\QRCode\Output\QRMarkupSVG;
use chillerlan\QRCode\QRCode;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QRCodeService
{
    /**
     * Menghasilkan token QR baru secara kriptografis, menonaktifkan token lama,
     * dan menyimpan hanya SHA-256 hash di database.
     *
     * @return array{qrToken: EventQrToken, rawToken: string, svgDataUri: string}
     */
    public function generateForEvent(Event $event, int $validMinutes = 120, ?string $generatedBy = null): array
    {
        return DB::transaction(function () use ($event, $validMinutes, $generatedBy) {
            $event->qrTokens()->where('is_active', true)->update(['is_active' => false]);

            $rawToken = 'KEV-'.Str::upper(Str::random(40));
            $tokenHash = hash('sha256', $rawToken);
            $manualCode = strtoupper(Str::random(8));

            $qrToken = EventQrToken::create([
                'event_id' => $event->id,
                'token_hash' => $tokenHash,
                'manual_code' => $manualCode,
                'expires_at' => now()->addMinutes($validMinutes),
                'is_active' => true,
                'generated_by' => $generatedBy ?? Auth::id() ?? $event->created_by,
            ]);

            if ($event->status === 'upcoming') {
                $event->update(['status' => 'ongoing']);
            }

            ActivityLog::record(
                'qr.generated',
                "Membuat token QR baru untuk kegiatan: {$event->title} (Kode Manual: {$manualCode})",
                EventQrToken::class,
                $qrToken->id
            );

            return [
                'qrToken' => $qrToken,
                'rawToken' => $rawToken,
                'svgDataUri' => $this->renderSvgDataUri($rawToken),
            ];
        });
    }

    public function deactivateToken(EventQrToken $qrToken): void
    {
        $qrToken->update(['is_active' => false]);

        ActivityLog::record(
            'qr.deactivated',
            "Menonaktifkan QR token kegiatan {$qrToken->event->title}",
            EventQrToken::class,
            $qrToken->id
        );
    }

    public function renderSvgDataUri(string $payload): string
    {
        return (new QRCode([
            'outputInterface' => QRMarkupSVG::class,
            'eccLevel' => EccLevel::M,
            'scale' => 8,
            'outputBase64' => true,
        ]))->render($payload);
    }

    public function renderPngBinary(string $payload): string
    {
        return (new QRCode([
            'outputInterface' => QRGdImagePNG::class,
            'eccLevel' => EccLevel::M,
            'scale' => 12,
            'outputBase64' => false,
        ]))->render($payload);
    }
}
