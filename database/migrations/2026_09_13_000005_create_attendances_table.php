<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('attendance_session_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('course_section_id')->constrained()->cascadeOnDelete();
            $table->string('status', 16);
            $table->timestamp('checked_in_at');
            $table->string('unique_key')->unique();
            $table->timestamps();

            $table->unique(['student_id', 'attendance_session_id']);
            $table->index(['student_id', 'checked_in_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
