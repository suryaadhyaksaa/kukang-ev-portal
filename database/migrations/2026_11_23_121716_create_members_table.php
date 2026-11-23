<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->foreignUlid('division_id')->nullable()->constrained('divisions')->nullOnDelete();
            $table->foreignUlid('position_id')->nullable()->constrained('positions')->nullOnDelete();
            $table->string('nim', 20)->unique();
            $table->string('full_name', 150);
            $table->string('email', 150)->unique();
            $table->string('phone', 25)->nullable();
            $table->year('cohort_year');
            $table->string('avatar_path')->nullable();
            $table->date('joined_at');
            $table->enum('status', ['active', 'inactive', 'alumni'])->default('active')->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['division_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
