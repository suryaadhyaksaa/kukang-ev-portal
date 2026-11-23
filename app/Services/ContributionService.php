<?php

namespace App\Services;

use App\Events\ContributionSubmitted;
use App\Events\ContributionVerified;
use App\Models\ActivityLog;
use App\Models\Contribution;
use App\Models\Member;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class ContributionService
{
    public function __construct(private readonly DocumentationService $documentationService) {}

    /**
     * @param  array<int, UploadedFile>  $attachments
     */
    public function submitContribution(Member $member, array $data, array $attachments = []): Contribution
    {
        return DB::transaction(function () use ($member, $data, $attachments) {
            $contribution = Contribution::create([
                'event_id' => $data['event_id'],
                'member_id' => $member->id,
                'category' => $data['category'],
                'work_description' => trim($data['work_description']),
                'work_output' => trim($data['work_output']),
                'obstacles' => ! empty($data['obstacles']) ? trim($data['obstacles']) : null,
                'status' => 'pending',
                'submitted_at' => now(),
            ]);

            if (! empty($attachments)) {
                $this->documentationService->uploadMultiple(
                    $attachments,
                    $member,
                    $contribution->event_id,
                    'Bukti Kontribusi - '.$contribution->category,
                    $contribution->id,
                    'internal_team'
                );
            }

            ActivityLog::record(
                'contribution.submitted',
                "{$member->full_name} ({$member->nim}) mengirim laporan kontribusi kategori {$contribution->category}.",
                Contribution::class,
                $contribution->id
            );

            DB::afterCommit(function () use ($contribution) {
                event(new ContributionSubmitted($contribution));
            });

            return $contribution->load(['event', 'member', 'documentations']);
        });
    }

    /**
     * @param  array<int, UploadedFile>  $attachments
     */
    public function updateContribution(Contribution $contribution, array $data, array $attachments = []): Contribution
    {
        return DB::transaction(function () use ($contribution, $data, $attachments) {
            $contribution->update([
                'event_id' => $data['event_id'],
                'category' => $data['category'],
                'work_description' => trim($data['work_description']),
                'work_output' => trim($data['work_output']),
                'obstacles' => ! empty($data['obstacles']) ? trim($data['obstacles']) : null,
                'status' => 'pending', // Reset ke pending saat anggota mengirim ulang revisi
            ]);

            if (! empty($attachments)) {
                $this->documentationService->uploadMultiple(
                    $attachments,
                    $contribution->member,
                    $contribution->event_id,
                    'Revisi Kontribusi - '.$contribution->category,
                    $contribution->id,
                    'internal_team'
                );
            }

            ActivityLog::record(
                'contribution.updated',
                "{$contribution->member->full_name} memperbarui laporan kontribusi.",
                Contribution::class,
                $contribution->id
            );

            return $contribution->fresh(['event', 'member', 'documentations']);
        });
    }

    public function verifyContribution(
        Contribution $contribution,
        User $reviewer,
        string $status,
        ?string $reviewerNotes = null
    ): Contribution {
        return DB::transaction(function () use ($contribution, $reviewer, $status, $reviewerNotes) {
            $contribution->update([
                'status' => $status,
                'reviewer_notes' => $reviewerNotes ? trim($reviewerNotes) : null,
                'verified_by' => $reviewer->id,
                'verified_at' => now(),
            ]);

            ActivityLog::record(
                'contribution.verified',
                "Pengurus {$reviewer->name} menetapkan status kontribusi milik {$contribution->member->full_name} menjadi ".strtoupper($status).'.',
                Contribution::class,
                $contribution->id,
                ['status' => $status]
            );

            DB::afterCommit(function () use ($contribution) {
                event(new ContributionVerified($contribution));
            });

            return $contribution->fresh(['event', 'member', 'verifier']);
        });
    }
}
