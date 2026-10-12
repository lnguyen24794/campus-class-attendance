<?php

namespace Tests\Unit\Repositories;

use App\Enums\AttendanceStatus;
use App\Enums\SessionStatus;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\CourseSection;
use App\Models\User;
use App\Repositories\AttendanceRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AttendanceRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private AttendanceRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(AttendanceRepository::class);
    }

    public function test_search_attendances_filters_by_keyword(): void
    {
        $student = User::factory()->student()->create();
        $lecturer = User::factory()->lecturer()->create();
        $section = CourseSection::query()->create([
            'code' => 'CT-SEARCH',
            'name' => 'Web Programming',
            'semester' => '2026-1',
            'lecturer_id' => $lecturer->id,
        ]);
        $session = AttendanceSession::query()->create([
            'id' => (string) Str::uuid(),
            'course_section_id' => $section->id,
            'opened_by' => $lecturer->id,
            'title' => 'Buổi Laravel API',
            'status' => SessionStatus::Open,
            'qr_token' => Str::random(40),
            'qr_expires_at' => now()->addHour(),
            'opens_at' => now(),
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
            'unique_key' => 'att_search_'.$session->id,
        ]);

        $byTitle = $this->repository->searchAttendances($student->id, ['q' => 'Laravel', 'limit' => 10, 'page' => 1]);
        $this->assertSame(1, $byTitle->total());

        $byCode = $this->repository->searchAttendances($student->id, ['q' => 'CT-SEARCH']);
        $this->assertSame(1, $byCode->total());

        $miss = $this->repository->searchAttendances($student->id, ['q' => 'zzz-no-match']);
        $this->assertSame(0, $miss->total());
    }

    public function test_list_attendances_by_session(): void
    {
        $student = User::factory()->student()->create(['code' => 'SVLIST']);
        $lecturer = User::factory()->lecturer()->create();
        $section = CourseSection::query()->create([
            'code' => 'CT-LIST',
            'name' => 'List',
            'semester' => '2026-1',
            'lecturer_id' => $lecturer->id,
        ]);
        $session = AttendanceSession::query()->create([
            'id' => (string) Str::uuid(),
            'course_section_id' => $section->id,
            'opened_by' => $lecturer->id,
            'title' => 'List session',
            'status' => SessionStatus::Open,
            'qr_token' => Str::random(40),
            'qr_expires_at' => now()->addHour(),
            'opens_at' => now(),
            'closes_at' => now()->addHour(),
            'late_after_minutes' => 15,
        ]);

        $this->repository->insertAttendance([
            'id' => (string) Str::uuid(),
            'student_id' => $student->id,
            'attendance_session_id' => $session->id,
            'course_section_id' => $section->id,
            'status' => AttendanceStatus::Late,
            'checked_in_at' => now(),
            'unique_key' => 'att_list_'.$session->id,
        ]);

        $rows = $this->repository->listAttendancesBySession($session->id);
        $this->assertCount(1, $rows);
        $this->assertSame('SVLIST', $rows->first()->student?->code);
    }
}
