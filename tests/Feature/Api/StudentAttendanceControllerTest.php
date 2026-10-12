<?php

namespace Tests\Feature\Api;

use App\Enums\AttendanceStatus;
use App\Enums\SessionStatus;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\CourseSection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class StudentAttendanceControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_requires_authentication(): void
    {
        $this->getJson('/api/v1/students/attendances')
            ->assertUnauthorized();
    }

    public function test_index_requires_student_role(): void
    {
        $lecturer = User::factory()->lecturer()->create();

        $this->actingAs($lecturer)
            ->getJson('/api/v1/students/attendances')
            ->assertForbidden();
    }

    public function test_index_validation_fails_when_limit_exceeds_maximum(): void
    {
        $student = User::factory()->student()->create();

        $this->actingAs($student)
            ->getJson('/api/v1/students/attendances?limit=1001')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['limit']);
    }

    public function test_index_returns_only_authenticated_student_attendances(): void
    {
        $student = User::factory()->student()->create();
        $other = User::factory()->student()->create();
        $lecturer = User::factory()->lecturer()->create();

        $section = CourseSection::query()->create([
            'id' => (string) Str::uuid(),
            'code' => 'CT101-01',
            'name' => 'Lập trình Web',
            'semester' => '2026-1',
            'lecturer_id' => $lecturer->id,
        ]);

        $session = AttendanceSession::query()->create([
            'id' => (string) Str::uuid(),
            'course_section_id' => $section->id,
            'opened_by' => $lecturer->id,
            'title' => 'Buổi 1',
            'status' => SessionStatus::Open,
            'qr_token' => Str::random(40),
            'qr_expires_at' => now()->addMinute(),
            'opens_at' => now()->subHour(),
            'closes_at' => now()->addHour(),
            'late_after_minutes' => 15,
        ]);

        Attendance::query()->create([
            'id' => (string) Str::uuid(),
            'student_id' => $student->id,
            'attendance_session_id' => $session->id,
            'course_section_id' => $section->id,
            'status' => AttendanceStatus::Present,
            'checked_in_at' => now(),
            'unique_key' => 'att_'.$student->id.'_'.$session->id,
        ]);

        Attendance::query()->create([
            'id' => (string) Str::uuid(),
            'student_id' => $other->id,
            'attendance_session_id' => $session->id,
            'course_section_id' => $section->id,
            'status' => AttendanceStatus::Present,
            'checked_in_at' => now(),
            'unique_key' => 'att_'.$other->id.'_'.$session->id,
        ]);

        $this->actingAs($student)
            ->getJson('/api/v1/students/attendances')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.course_section_code', 'CT101-01')
            ->assertJsonPath('data.0.session_title', 'Buổi 1');
    }
}
