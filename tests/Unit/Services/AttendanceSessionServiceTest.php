<?php

namespace Tests\Unit\Services;

use App\Models\CourseSection;
use App\Models\Period;
use App\Models\User;
use App\Services\AttendanceSessionService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceSessionServiceTest extends TestCase
{
    use RefreshDatabase;

    private AttendanceSessionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(AttendanceSessionService::class);
    }

    public function test_create_rejects_section_not_owned_by_lecturer(): void
    {
        $lecturer = User::factory()->lecturer()->create();
        $other = User::factory()->lecturer()->create();
        $section = CourseSection::query()->create([
            'code' => 'CT-OWN',
            'name' => 'Owned',
            'semester' => '2026-1',
            'lecturer_id' => $other->id,
        ]);

        $this->expectException(ModelNotFoundException::class);

        $this->service->createAttendanceSession($lecturer, [
            'course_section_id' => $section->id,
            'title' => 'Nope',
        ]);
    }

    public function test_create_with_period_uses_period_end_when_still_open(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-10 07:10:00'));

        $lecturer = User::factory()->lecturer()->create();
        $section = CourseSection::query()->create([
            'code' => 'CT-P1',
            'name' => 'Period open',
            'semester' => '2026-1',
            'lecturer_id' => $lecturer->id,
        ]);
        $period = Period::query()->create([
            'number' => 1,
            'name' => 'Tiết 1',
            'starts_at' => '07:00:00',
            'ends_at' => '07:50:00',
        ]);

        $session = $this->service->createAttendanceSession($lecturer, [
            'course_section_id' => $section->id,
            'title' => 'Between period',
            'period_id' => $period->id,
        ]);

        $this->assertTrue($session->opens_at->equalTo(Carbon::parse('2026-10-10 07:10:00')));
        $this->assertTrue($session->closes_at->equalTo(Carbon::parse('2026-10-10 07:50:00')));
        $this->assertTrue($session->qr_expires_at->equalTo($session->closes_at));

        Carbon::setTestNow();
    }

    public function test_create_with_period_uses_full_period_when_already_ended(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-10 08:30:00'));

        $lecturer = User::factory()->lecturer()->create();
        $section = CourseSection::query()->create([
            'code' => 'CT-P2',
            'name' => 'Period ended',
            'semester' => '2026-1',
            'lecturer_id' => $lecturer->id,
        ]);
        $period = Period::query()->create([
            'number' => 1,
            'name' => 'Tiết 1',
            'starts_at' => '07:00:00',
            'ends_at' => '07:50:00',
        ]);

        $session = $this->service->createAttendanceSession($lecturer, [
            'course_section_id' => $section->id,
            'title' => 'After period',
            'period_id' => $period->id,
        ]);

        $this->assertTrue($session->opens_at->equalTo(Carbon::parse('2026-10-10 07:00:00')));
        $this->assertTrue($session->closes_at->equalTo(Carbon::parse('2026-10-10 07:50:00')));

        Carbon::setTestNow();
    }

    public function test_create_without_period_uses_duration_minutes(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-10 09:00:00'));

        $lecturer = User::factory()->lecturer()->create();
        $section = CourseSection::query()->create([
            'code' => 'CT-P3',
            'name' => 'No period',
            'semester' => '2026-1',
            'lecturer_id' => $lecturer->id,
        ]);

        $session = $this->service->createAttendanceSession($lecturer, [
            'course_section_id' => $section->id,
            'title' => 'Duration only',
            'duration_minutes' => 45,
        ]);

        $this->assertTrue($session->closes_at->equalTo(Carbon::parse('2026-10-10 09:45:00')));
        $this->assertNull($session->period_id);

        Carbon::setTestNow();
    }
}
