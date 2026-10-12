<?php

namespace Tests\Feature\Api;

use App\Enums\SessionStatus;
use App\Models\AttendanceSession;
use App\Models\CourseSection;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PublicCheckInControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_show_returns_session_meta(): void
    {
        [$student, $session] = $this->createOpenSessionContext();

        $this->getJson('/api/v1/public/check-in/'.$session->qr_token)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.expired', false)
            ->assertJsonPath('data.session.title', 'Buổi public')
            ->assertJsonPath('data.session.course_section_code', 'CT-PUB');

        $this->getJson('/api/v1/public/check-in/missing-token')
            ->assertOk()
            ->assertJsonPath('data.expired', true)
            ->assertJsonPath('data.session', null);

        unset($student);
    }

    public function test_store_checks_in_by_student_code(): void
    {
        [$student, $session] = $this->createOpenSessionContext();

        $this->postJson('/api/v1/public/check-in/'.$session->qr_token, [
            'identifier' => $student->code,
        ])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'present')
            ->assertJsonPath('message', 'Bạn đã điểm danh Lập trình Web - Không gắn tiết - Đúng giờ');
    }

    public function test_store_rejects_unknown_identifier(): void
    {
        [, $session] = $this->createOpenSessionContext();

        $this->postJson('/api/v1/public/check-in/'.$session->qr_token, [
            'identifier' => 'UNKNOWN',
        ])
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonValidationErrors(['identifier']);
    }

    public function test_store_rejects_non_student_identifier(): void
    {
        [, $session] = $this->createOpenSessionContext();
        $lecturer = User::factory()->lecturer()->create(['code' => 'GVNOTSTU']);

        $this->postJson('/api/v1/public/check-in/'.$session->qr_token, [
            'identifier' => $lecturer->code,
        ])
            ->assertUnprocessable()
            ->assertJsonPath('success', false);
    }

    public function test_store_maps_attendance_exception(): void
    {
        [$student, $session] = $this->createOpenSessionContext();
        $session->forceFill(['qr_expires_at' => now()->subSecond()])->save();

        $this->postJson('/api/v1/public/check-in/'.$session->qr_token, [
            'identifier' => $student->email,
        ])
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Mã QR đã hết hạn. Vui lòng quét lại mã mới trên màn hình lớp học.');
    }

    /**
     * @return array{0: User, 1: AttendanceSession}
     */
    private function createOpenSessionContext(): array
    {
        $lecturer = User::factory()->lecturer()->create();
        $student = User::factory()->student()->create(['code' => 'SVPUB'.uniqid()]);

        $section = CourseSection::query()->create([
            'id' => (string) Str::uuid(),
            'code' => 'CT-PUB',
            'name' => 'Lập trình Web',
            'semester' => '2026-1',
            'lecturer_id' => $lecturer->id,
        ]);

        Enrollment::query()->create([
            'student_id' => $student->id,
            'course_section_id' => $section->id,
        ]);

        $session = AttendanceSession::query()->create([
            'id' => (string) Str::uuid(),
            'course_section_id' => $section->id,
            'opened_by' => $lecturer->id,
            'title' => 'Buổi public',
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
