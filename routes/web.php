<?php

use App\Http\Controllers\DocumentationFileController;
use App\Livewire\Attendance\QrScanner;
use App\Livewire\Contributions\ContributionIndex;
use App\Livewire\Dashboard\DashboardOverview;
use App\Livewire\Documentation\DocumentationGallery;
use App\Livewire\Events\EventIndex;
use App\Livewire\Events\EventShow;
use App\Livewire\Members\DivisionPositionManager;
use App\Livewire\Members\MemberIndex;
use App\Livewire\Members\MemberProfile;
use App\Livewire\Reports\ReportSyncCenter;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardOverview::class)->name('dashboard');
    Route::get('/profile', MemberProfile::class)->name('profile.show');

    Route::get('/attendance/scanner', QrScanner::class)->name('attendance.scanner');
    Route::get('/events', EventIndex::class)->name('events.index');
    Route::get('/events/{event}', EventShow::class)->name('events.show');

    Route::get('/contributions', ContributionIndex::class)->name('contributions.index');
    Route::get('/documentation', DocumentationGallery::class)->name('documentation.index');
    Route::get('/documentation/{documentation}/preview', [DocumentationFileController::class, 'preview'])->name('documentation.preview');
    Route::get('/documentation/{documentation}/download', [DocumentationFileController::class, 'download'])->name('documentation.download');

    Route::middleware(['role:super_admin|admin'])->group(function () {
        Route::get('/members', MemberIndex::class)->name('members.index');
        Route::get('/members/{member}', MemberProfile::class)->name('members.show');
        Route::get('/divisions', DivisionPositionManager::class)->name('divisions.index');
        Route::get('/reports', ReportSyncCenter::class)->name('reports.index');
    });
});
