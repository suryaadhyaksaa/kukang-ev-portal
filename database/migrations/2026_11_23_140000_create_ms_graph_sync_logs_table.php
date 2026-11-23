<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ms_graph_sync_logs')) {
            Schema::create('ms_graph_sync_logs', function (Blueprint $table) {
                $table->ulid('id')->primary();$table->string('sync_type')->default('full_workbook');
                $table->string('status')->default('processing');$table->string('workbook_id')->nullable();
                $table->unsignedInteger('records_synced')->default(0);$table->text('error_message')->nullable();
                $table->foreignUlid('triggered_by')->nullable()->constrained('users')->nullOnDelete();$table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();$table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ms_graph_sync_logs');
    }
};
