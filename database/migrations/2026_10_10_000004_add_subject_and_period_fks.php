<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_sections', function (Blueprint $table) {
            $table->foreignUuid('subject_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });

        Schema::table('attendance_sessions', function (Blueprint $table) {
            $table->foreignUuid('period_id')->nullable()->after('course_section_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('attendance_sessions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('period_id');
        });

        Schema::table('course_sections', function (Blueprint $table) {
            $table->dropConstrainedForeignId('subject_id');
        });
    }
};
