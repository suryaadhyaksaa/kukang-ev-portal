<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignUlid('member_id')->constrained('members')->cascadeOnDelete();
            $table->foreignUlid('qr_token_id')->nullable()->constrained('event_qr_tokens')->nullOnDelete();
            $table->timestamp('checked_in_at')->useCurrent()->index();
            $table->enum('status', ['present', 'late', 'excused'])->default('present')->index();
            $table->enum('check_in_method', ['qr_scan', 'manual_code', 'admin_override'])->default('qr_scan');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            // Keamanan mutlak: 1 anggota maksimal 1 absensi per kegiatan
            $table->unique(['member_id', 'event_id']);
        });

        Schema::create('contributions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignUlid('member_id')->constrained('members')->cascadeOnDelete();
            $table->enum('category', [
                'Hardware Development',
                'Electrical System',
                'Software Development',
                'Mechanical Development',
                'Administration',
                'Sponsorship',
                'Public Relations',
                'Event Management',
                'Documentation',
                'Research & Development',
                'Other',
            ])->index();
            $table->text('work_description');
            $table->text('work_output');
            $table->text('obstacles')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'revision_required'])->default('pending')->index();
            $table->text('reviewer_notes')->nullable();
            $table->foreignUlid('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('submitted_at')->useCurrent()->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['member_id', 'event_id']);
        });

        Schema::create('documentations', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignUlid('contribution_id')->nullable()->constrained('contributions')->nullOnDelete();
            $table->foreignUlid('uploaded_by_member_id')->constrained('members')->cascadeOnDelete();
            $table->string('title', 200);
            $table->string('original_filename');
            $table->string('stored_path');
            $table->string('disk', 30)->default('local');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('file_size');
            $table->enum('visibility', ['internal_team', 'private_member'])->default('internal_team')->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentations');
        Schema::dropIfExists('contributions');
        Schema::dropIfExists('attendances');
    }
};
