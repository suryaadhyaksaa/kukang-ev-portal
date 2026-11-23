<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('event_code', 30)->unique();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->date('event_date')->index();
            $table->time('start_time');
            $table->time('end_time');
            $table->string('location', 200);
            $table->foreignUlid('division_id')->nullable()->constrained('divisions')->nullOnDelete();
            $table->foreignUlid('pic_member_id')->nullable()->constrained('members')->nullOnDelete();
            $table->enum('status', ['upcoming', 'ongoing', 'completed', 'cancelled'])->default('upcoming')->index();
            $table->timestamp('attendance_open_at')->nullable()->index();
            $table->timestamp('attendance_close_at')->nullable()->index();
            $table->foreignUlid('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('event_participants', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignUlid('member_id')->constrained('members')->cascadeOnDelete();
            $table->boolean('is_mandatory')->default(true);
            $table->timestamps();

            $table->unique(['event_id', 'member_id']);
        });

        Schema::create('event_qr_tokens', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('event_id')->constrained('events')->cascadeOnDelete();
            $table->string('token_hash', 64)->unique(); // SHA-256 hash dari raw token
            $table->string('manual_code', 16)->unique(); // Kode pendek cadangan untuk input manual
            $table->timestamp('expires_at')->index();
            $table->boolean('is_active')->default(true)->index();
            $table->foreignUlid('generated_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_qr_tokens');
        Schema::dropIfExists('event_participants');
        Schema::dropIfExists('events');
    }
};
