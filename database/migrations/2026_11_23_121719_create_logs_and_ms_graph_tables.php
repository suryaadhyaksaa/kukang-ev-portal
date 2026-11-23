<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 80)->index();
            $table->text('description');
            $table->string('entity_type', 100)->nullable()->index();
            $table->string('entity_id', 36)->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });

        Schema::create('excel_sync_logs', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('idempotency_key', 100)->unique();
            $table->string('target_table', 60)->index(); // MembersTable, EventsTable, AttendanceTable, dll.
            $table->string('entity_type', 100);
            $table->string('entity_id', 36);
            $table->enum('operation', ['insert', 'update', 'full_sync'])->default('insert');
            $table->enum('status', ['queued', 'processing', 'success', 'failed'])->default('queued')->index();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->text('error_message')->nullable();
            $table->foreignUlid('triggered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });

        // Penyimpanan terenkripsi untuk OAuth Delegated Token Microsoft Graph
        Schema::create('microsoft_graph_tokens', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('connected_by')->constrained('users')->cascadeOnDelete();
            $table->text('access_token'); // Di-encrypt via Eloquent cast 'encrypted'
            $table->text('refresh_token'); // Di-encrypt via Eloquent cast 'encrypted'
            $table->timestamp('expires_at');
            $table->string('scope')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('microsoft_graph_tokens');
        Schema::dropIfExists('excel_sync_logs');
        Schema::dropIfExists('activity_logs');
    }
};
