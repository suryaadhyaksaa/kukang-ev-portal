<?php

namespace App\Http\Controllers;

use App\Models\Documentation;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentationFileController extends Controller
{
    public function preview(Documentation $documentation): StreamedResponse
    {
        Gate::authorize('view', $documentation);

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk($documentation->disk);

        abort_unless($disk->exists($documentation->stored_path), 404);

        return $disk->response(
            $documentation->stored_path,
            $documentation->original_filename,
            ['Content-Type' => $documentation->mime_type]
        );
    }

    public function download(Documentation $documentation): StreamedResponse
    {
        Gate::authorize('view', $documentation);

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk($documentation->disk);

        abort_unless($disk->exists($documentation->stored_path), 404);

        return $disk->download(
            $documentation->stored_path,
            $documentation->original_filename
        );
    }
}
