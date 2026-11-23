<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('divisions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('code', 30)->unique();
            $table->string('name', 120)->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('positions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('division_id')->nullable()->constrained('divisions')->nullOnDelete();
            $table->string('name', 120);
            $table->unsignedTinyInteger('hierarchy_level')->default(10);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['division_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('positions');
        Schema::dropIfExists('divisions');
    }
};
