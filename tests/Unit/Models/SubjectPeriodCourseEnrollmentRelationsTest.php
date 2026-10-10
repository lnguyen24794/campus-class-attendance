<?php

namespace Tests\Unit\Models;

use App\Enums\AttendanceStatus;
use App\Enums\SessionStatus;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\CourseSection;
use App\Models\Enrollment;
use App\Models\Period;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubjectPeriodCourseEnrollmentRelationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_model_relations(): void
    {
        $subject = Subject::query()->create([
            'code' => 'REL01',
            'name' => 'Relations',
            'credits' => 3,
        ]);
        $period = Period::query()->create([
            'number' => 1,
            'name' => 'P1',
            'starts_at' => '07:00',
            'ends_at' => '07:50',
        ]);
        $lecturer = User::factory()->lecturer()->create(['code' => 'GVREL']);
        $student = User::factory()->student()->create(['code' => 'SVREL']);

        $section = CourseSection::query()->create([
            'subject_id' => $subject->id,
            'code' => 'RELSEC',
            'name' => 'Rel Section',
            'semester' => '2026-1',
            'lecturer_id' => $lecturer->id,
        ]);

        $enrollment = Enrollment::query()->create([
            'student_id' => $student->id,
            'course_section_id' => $section->id,
        ]);

        $session = AttendanceSession::query()->create([
            'course_section_id' => $section->id,
            'period_id' => $period->id,
            'opened_by' => $lecturer->id,
            'title' => 'Rel Session',
            'status' => SessionStatus::Open,
            'qr_token' => 'rel-token-1',
            'qr_expires_at' => now()->addHour(),
            'opens_at' => now()->subMinute(),
            'closes_at' => now()->addHour(),
            'late_after_minutes' => 15,
        ]);

        $attendance = Attendance::query()->create([
            'student_id' => $student->id,
            'attendance_session_id' => $session->id,
            'course_section_id' => $section->id,
            'status' => AttendanceStatus::Present,
            'checked_in_at' => now(),
            'unique_key' => 'rel-unique-1',
        ]);

        $this->assertTrue($subject->courseSections->contains($section));
        $this->assertTrue($period->attendanceSessions->contains($session));
        $this->assertTrue($section->subject->is($subject));
        $this->assertTrue($section->lecturer->is($lecturer));
        $this->assertTrue($section->enrollments->contains($enrollment));
        $this->assertTrue($section->attendanceSessions->contains($session));
        $this->assertTrue($enrollment->student->is($student));
        $this->assertTrue($enrollment->courseSection->is($section));
        $this->assertTrue($session->courseSection->is($section));
        $this->assertTrue($session->period->is($period));
        $this->assertTrue($session->openedBy->is($lecturer));
        $this->assertTrue($session->attendances->contains($attendance));
        $this->assertTrue($attendance->student->is($student));
        $this->assertTrue($attendance->attendanceSession->is($session));
        $this->assertTrue($attendance->courseSection->is($section));
    }
}
