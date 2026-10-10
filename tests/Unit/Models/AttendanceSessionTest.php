<?php

namespace Tests\Unit\Models;

use App\Enums\SessionStatus;
use App\Models\AttendanceSession;
use App\Models\CourseSection;
use App\Models\Subject;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceSessionTest extends TestCase
{
    use RefreshDatabase;

    private function makeSession(array $overrides = []): AttendanceSession
    {
        $lecturer = User::factory()->lecturer()->create(['code' => 'GVSESS'.uniqid()]);
        $subject = Subject::query()->create([
            'code' => 'SESS'.uniqid(),
            'name' => 'Session Subject',
            'credits' => 3,
        ]);
        $section = CourseSection::query()->create([
            'subject_id' => $subject->id,
            'code' => 'SEC'.uniqid(),
            'name' => 'Section',
            'semester' => '2026-1',
            'lecturer_id' => $lecturer->id,
        ]);

        return AttendanceSession::query()->create(array_merge([
            'course_section_id' => $section->id,
            'opened_by' => $lecturer->id,
            'title' => 'Test Session',
            'status' => SessionStatus::Open,
            'qr_token' => 'qr-'.uniqid(),
            'qr_expires_at' => now()->addMinutes(30),
            'opens_at' => now()->subMinutes(5),
            'closes_at' => now()->addMinutes(55),
            'late_after_minutes' => 15,
        ], $overrides));
    }

    public function test_is_open_true_and_false(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-10 10:00:00'));

        $open = $this->makeSession([
            'status' => SessionStatus::Open,
            'opens_at' => Carbon::parse('2026-10-10 09:00:00'),
            'closes_at' => Carbon::parse('2026-10-10 11:00:00'),
        ]);
        $this->assertTrue($open->isOpen());

        $closedStatus = $this->makeSession([
            'status' => SessionStatus::Closed,
            'opens_at' => Carbon::parse('2026-10-10 09:00:00'),
            'closes_at' => Carbon::parse('2026-10-10 11:00:00'),
        ]);
        $this->assertFalse($closedStatus->isOpen());

        $outsideWindow = $this->makeSession([
            'status' => SessionStatus::Open,
            'opens_at' => Carbon::parse('2026-10-10 08:00:00'),
            'closes_at' => Carbon::parse('2026-10-10 09:00:00'),
        ]);
        $this->assertFalse($outsideWindow->isOpen());

        Carbon::setTestNow();
    }

    public function test_is_qr_expired_true_and_false(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-10 10:00:00'));

        $valid = $this->makeSession([
            'qr_expires_at' => Carbon::parse('2026-10-10 10:30:00'),
        ]);
        $this->assertFalse($valid->isQrExpired());

        $expired = $this->makeSession([
            'qr_expires_at' => Carbon::parse('2026-10-10 09:59:00'),
        ]);
        $this->assertTrue($expired->isQrExpired());

        Carbon::setTestNow();
    }
}
