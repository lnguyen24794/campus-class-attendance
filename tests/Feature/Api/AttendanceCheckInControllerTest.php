<?php

namespace Tests\Feature\Api;

use App\Enums\SessionStatus;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\CourseSection;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AttendanceCheckInControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_check_in_succeeds_when_rules_pass(): void
    {
        [$student, $session] = $this->createOpenSessionContext();

        $this->actingAs($student)
            ->postJson('/api/v1/students/attendances/check-in', [
                'session_qr_token' => $session->qr_token,
            ])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'present')
            ->assertJsonPath('message', 'Bạn đã điểm danh Lập trình Web - Không gắn tiết - Đúng giờ');

        $this->assertDatabaseHas('attendances', [
            'student_id' => $student->id,
            'attendance_session_id' => $session->id,
            'status' => 'present',
        ]);
    }

    public function test_check_in_rejects_expired_qr(): void
    {
        [$student, $session] = $this->createOpenSessionContext();
        $session->forceFill(['qr_expires_at' => now()->subSecond()])->save();

        $this->actingAs($student)
            ->postJson('/api/v1/students/attendances/check-in', [
                'session_qr_token' => $session->qr_token,
            ])
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('code', 'MSG-01');
    }

    public function test_check_in_rejects_when_student_not_enrolled(): void
    {
        [$student, $session] = $this->createOpenSessionContext(enroll: false);

        $this->actingAs($student)
            ->postJson('/api/v1/students/attendances/check-in', [
                'session_qr_token' => $session->qr_token,
            ])
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('code', 'MSG-02');
    }

    public function test_check_in_rejects_duplicate_attendance(): void
    {
        [$student, $session] = $this->createOpenSessionContext();

        Attendance::query()->create([
            'id' => (string) Str::uuid(),
            'student_id' => $student->id,
            'attendance_session_id' => $session->id,
            'course_section_id' => $session->course_section_id,
            'status' => 'present',
            'checked_in_at' => now()->subMinute(),
            'unique_key' => 'att_'.$student->id.'_'.$session->id,
        ]);

        $this->actingAs($student)
            ->postJson('/api/v1/students/attendances/check-in', [
                'session_qr_token' => $session->qr_token,
            ])
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('code', 'MSG-03');
    }

    public function test_check_in_rejects_when_session_closed(): void
    {
        [$student, $session] = $this->createOpenSessionContext();
        $session->forceFill([
            'status' => SessionStatus::Closed,
            'closes_at' => now()->subMinute(),
        ])->save();

        $this->actingAs($student)
            ->postJson('/api/v1/students/attendances/check-in', [
                'session_qr_token' => $session->qr_token,
            ])
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('code', 'MSG-04');
    }

    public function test_lecturer_can_open_attendance_session(): void
    {
        $lecturer = User::factory()->lecturer()->create();
        $section = CourseSection::query()->create([
            'id' => (string) Str::uuid(),
            'code' => 'CT101-01',
            'name' => 'Lập trình Web',
            'semester' => '2026-1',
            'lecturer_id' => $lecturer->id,
        ]);

        $this->actingAs($lecturer)
            ->postJson('/api/v1/lecturers/attendance-sessions', [
                'course_section_id' => $section->id,
                'title' => 'Buổi 3 - Laravel API',
                'late_after_minutes' => 10,
            ])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['session_id', 'qr_token', 'qr_expires_at', 'closes_at']]);
    }

    public function test_refresh_qr_keeps_fixed_expiry(): void
    {
        $lecturer = User::factory()->lecturer()->create();
        $section = CourseSection::query()->create([
            'id' => (string) Str::uuid(),
            'code' => 'CT101-01',
            'name' => 'Lập trình Web',
            'semester' => '2026-1',
            'lecturer_id' => $lecturer->id,
        ]);

        $expiresAt = now()->addHour()->startOfSecond();
        $session = AttendanceSession::query()->create([
            'id' => (string) Str::uuid(),
            'course_section_id' => $section->id,
            'opened_by' => $lecturer->id,
            'title' => 'Buổi 1',
            'status' => SessionStatus::Open,
            'qr_token' => Str::random(40),
            'qr_expires_at' => $expiresAt,
            'opens_at' => now()->subMinutes(5),
            'closes_at' => $expiresAt,
            'late_after_minutes' => 15,
        ]);
        $oldToken = $session->qr_token;

        $refreshed = app(\App\Services\AttendanceSessionService::class)->refreshQrToken($session);

        $this->assertNotSame($oldToken, $refreshed->qr_token);
        $this->assertTrue($expiresAt->equalTo($refreshed->qr_expires_at));
    }

    public function test_check_in_marks_late_after_grace_window(): void
    {
        [$student, $session] = $this->createOpenSessionContext();
        $session->forceFill([
            'opens_at' => now()->subMinutes(20),
            'late_after_minutes' => 15,
            'qr_expires_at' => now()->addHour(),
            'closes_at' => now()->addHour(),
        ])->save();

        $this->actingAs($student)
            ->postJson('/api/v1/students/attendances/check-in', [
                'session_qr_token' => $session->qr_token,
            ])
            ->assertCreated()
            ->assertJsonPath('data.status', 'late')
            ->assertJsonPath('data.status_label', 'Đi muộn');
    }

    /**
     * @return array{0: User, 1: AttendanceSession}
     */
    private function createOpenSessionContext(bool $enroll = true): array
    {
        $lecturer = User::factory()->lecturer()->create();
        $student = User::factory()->student()->create();

        $section = CourseSection::query()->create([
            'id' => (string) Str::uuid(),
            'code' => 'CT101-01',
            'name' => 'Lập trình Web',
            'semester' => '2026-1',
            'lecturer_id' => $lecturer->id,
        ]);

        if ($enroll) {
            Enrollment::query()->create([
                'student_id' => $student->id,
                'course_section_id' => $section->id,
            ]);
        }

        $session = AttendanceSession::query()->create([
            'id' => (string) Str::uuid(),
            'course_section_id' => $section->id,
            'opened_by' => $lecturer->id,
            'title' => 'Buổi 1',
            'status' => SessionStatus::Open,
            'qr_token' => Str::random(40),
            'qr_expires_at' => now()->addMinute(),
            'opens_at' => now()->subMinutes(5),
            'closes_at' => now()->addHour(),
            'late_after_minutes' => 15,
        ]);

        return [$student, $session];
    }
}
