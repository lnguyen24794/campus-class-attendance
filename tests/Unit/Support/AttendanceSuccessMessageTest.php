<?php

namespace Tests\Unit\Support;

use App\Enums\AttendanceStatus;
use App\Enums\SessionStatus;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\CourseSection;
use App\Models\Period;
use App\Models\Subject;
use App\Models\User;
use App\Support\AttendanceSuccessMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AttendanceSuccessMessageTest extends TestCase
{
    use RefreshDatabase;

    public function test_from_uses_period_number_when_name_missing(): void
    {
        $lecturer = User::factory()->lecturer()->create();
        $student = User::factory()->student()->create();
        $subject = Subject::query()->create([
            'code' => 'SUBMSG',
            'name' => 'Lập trình Web',
            'credits' => 3,
        ]);
        $section = CourseSection::query()->create([
            'subject_id' => $subject->id,
            'code' => 'CT-MSG',
            'name' => 'Section MSG',
            'semester' => '2026-1',
            'lecturer_id' => $lecturer->id,
        ]);
        $period = Period::query()->create([
            'number' => 2,
            'name' => 'placeholder',
            'starts_at' => '08:00:00',
            'ends_at' => '08:50:00',
        ]);
        $session = AttendanceSession::query()->create([
            'id' => (string) Str::uuid(),
            'course_section_id' => $section->id,
            'period_id' => $period->id,
            'opened_by' => $lecturer->id,
            'title' => 'MSG session',
            'status' => SessionStatus::Open,
            'qr_token' => Str::random(40),
            'qr_expires_at' => now()->addHour(),
            'opens_at' => now(),
            'closes_at' => now()->addHour(),
            'late_after_minutes' => 15,
        ]);
        $attendance = Attendance::query()->create([
            'id' => (string) Str::uuid(),
            'student_id' => $student->id,
            'attendance_session_id' => $session->id,
            'course_section_id' => $section->id,
            'status' => AttendanceStatus::Present,
            'checked_in_at' => now(),
            'unique_key' => 'att_msg_'.$session->id,
        ]);

        $attendance->load(['courseSection.subject', 'attendanceSession.period']);
        // Simulate missing period name in memory (DB column is NOT NULL).
        $attendance->attendanceSession->period->name = null;

        $message = AttendanceSuccessMessage::from($attendance);

        $this->assertSame(
            'Bạn đã điểm danh Lập trình Web - Tiết 2 - Đúng giờ',
            $message,
        );
    }
}
