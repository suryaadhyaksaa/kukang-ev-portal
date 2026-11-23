<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Documentation;
use App\Models\Member;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentationService
{
    public const ALLOWED_MIMES = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'video/mp4',
        'application/pdf',
    ];

    /**
     * @param  array<int, UploadedFile>  $files
     * @return array<int, Documentation>
     */
    public function uploadMultiple(
        array $files,
        Member $uploader,
        string $eventId,
        string $baseTitle,
        ?string $contributionId = null,
        string $visibility = 'internal_team'
    ): array {
        return DB::transaction(function () use ($files, $uploader, $eventId, $baseTitle, $contributionId, $visibility) {
            $savedDocs = [];
            $totalFiles = count($files);

            foreach ($files as $index => $file) {
                $mime = $file->getMimeType() ?? $file->getClientMimeType();
                $ext = strtolower($file->getClientOriginalExtension());
                $safeName = Str::ulid().'.'.$ext;
                $directory = 'documentations/'.now()->format('Y/m');

                $storedPath = $file->storeAs($directory, $safeName, 'local');

                $title = $totalFiles > 1
                    ? "{$baseTitle} (#".($index + 1).')'
                    : $baseTitle;

                $doc = Documentation::create([
                    'event_id' => $eventId,
                    'contribution_id' => $contributionId,
                    'uploaded_by_member_id' => $uploader->id,
                    'title' => $title,
                    'original_filename' => $file->getClientOriginalName(),
                    'stored_path' => $storedPath,
                    'disk' => 'local',
                    'mime_type' => $mime,
                    'file_size' => $file->getSize(),
                    'visibility' => in_array($visibility, ['internal_team', 'private_member'], true) ? $visibility : 'internal_team',
                ]);

                $savedDocs[] = $doc;
            }

            ActivityLog::record(
                'documentation.uploaded',
                "{$uploader->full_name} mengunggah ".count($savedDocs)." file dokumentasi ({$baseTitle}).",
                Documentation::class,
                $savedDocs[0]->id ?? null,
                ['event_id' => $eventId, 'count' => count($savedDocs)]
            );

            return $savedDocs;
        });
    }

    public function deleteDocumentation(Documentation $documentation): void
    {
        DB::transaction(function () use ($documentation) {
            Storage::disk($documentation->disk)->delete($documentation->stored_path);
            $title = $documentation->title;
            $docId = $documentation->id;
            $documentation->delete();

            ActivityLog::record(
                'documentation.deleted',
                "Menghapus file dokumentasi: {$title}",
                Documentation::class,
                $docId
            );
        });
    }
}
