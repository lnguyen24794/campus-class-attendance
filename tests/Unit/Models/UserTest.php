<?php

namespace Tests\Unit\Models;

use App\Enums\AttendanceStatus;
use App\Enums\SessionStatus;
use App\Enums\UserRole;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\CourseSection;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_helpers(): void
    {
        $student = User::factory()->student()->create(['code' => 'SVROLEU']);
        $lecturer = User::factory()->lecturer()->create(['code' => 'GVROLEU']);
        $admin = User::factory()->create(['role' => UserRole::Admin, 'code' => 'ADMROLEU']);

        $this->assertTrue($student->isStudent());
        $this->assertFalse($student->isLecturer());
        $this->assertFalse($student->isAdmin());

        $this->assertTrue($lecturer->isLecturer());
        $this->assertTrue($admin->isAdmin());
    }

    public function test_attendances_relation(): void
    {
        $student = User::factory()->student()->create(['code' => 'SVATT']);
        $lecturer = User::factory()->lecturer()->create(['code' => 'GVATT']);
        $subject = Subject::query()->create(['code' => 'ATT1', 'name' => 'Att', 'credits' => 3]);
        $section = CourseSection::query()->create([
            'subject_id' => $subject->id,
            'code' => 'ATTSEC',
            'name' => 'Att Section',
            'semester' => '2026-1',
            'lecturer_id' => $lecturer->id,
        ]);
        $session = AttendanceSession::query()->create([
            'course_section_id' => $section->id,
            'opened_by' => $lecturer->id,
            'title' => 'Session',
            'status' => SessionStatus::Open,
            'qr_token' => 'token-att-1',
            'qr_expires_at' => now()->addHour(),
            'opens_at' => now()->subMinute(),
            'closes_at' => now()->addHour(),
            'late_after_minutes' => 15,
        ]);

        Attendance::query()->create([
            'student_id' => $student->id,
            'attendance_session_id' => $session->id,
            'course_section_id' => $section->id,
            'status' => AttendanceStatus::Present,
            'checked_in_at' => now(),
            'unique_key' => 'unique-att-1',
        ]);

        $this->assertCount(1, $student->attendances);
        $this->assertTrue($student->attendances->first()->is($session->attendances->first()));
    }
}
