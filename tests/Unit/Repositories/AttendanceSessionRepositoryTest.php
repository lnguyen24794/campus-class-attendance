<?php

namespace Tests\Unit\Repositories;

use App\Enums\SessionStatus;
use App\Models\AttendanceSession;
use App\Models\CourseSection;
use App\Models\User;
use App\Repositories\AttendanceSessionRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AttendanceSessionRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private AttendanceSessionRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(AttendanceSessionRepository::class);
    }

    public function test_paginate_filters_by_q_status_and_lecturer(): void
    {
        $lecturer = User::factory()->lecturer()->create(['code' => 'GVREP']);
        $admin = User::factory()->create(['role' => \App\Enums\UserRole::Admin, 'code' => 'ADMREP']);
        $section = CourseSection::query()->create([
            'code' => 'CT-REP',
            'name' => 'Repo Section',
            'semester' => '2026-1',
            'lecturer_id' => $lecturer->id,
        ]);

        AttendanceSession::query()->create([
            'id' => (string) Str::uuid(),
            'course_section_id' => $section->id,
            'opened_by' => $lecturer->id,
            'title' => 'Open Laravel',
            'status' => SessionStatus::Open,
            'qr_token' => Str::random(40),
            'qr_expires_at' => now()->addHour(),
            'opens_at' => now(),
            'closes_at' => now()->addHour(),
            'late_after_minutes' => 15,
        ]);
        AttendanceSession::query()->create([
            'id' => (string) Str::uuid(),
            'course_section_id' => $section->id,
            'opened_by' => $lecturer->id,
            'title' => 'Closed Node',
            'status' => SessionStatus::Closed,
            'qr_token' => Str::random(40),
            'qr_expires_at' => now()->subHour(),
            'opens_at' => now()->subHours(2),
            'closes_at' => now()->subHour(),
            'late_after_minutes' => 15,
        ]);

        $all = $this->repository->paginateAttendanceSessions($admin, ['limit' => 10, 'page' => 1]);
        $this->assertSame(2, $all->total());

        $open = $this->repository->paginateAttendanceSessions($admin, ['status' => 'open']);
        $this->assertSame(1, $open->total());

        $q = $this->repository->paginateAttendanceSessions($admin, ['q' => 'Laravel']);
        $this->assertSame(1, $q->total());

        $bySection = $this->repository->paginateAttendanceSessions($admin, ['q' => 'CT-REP']);
        $this->assertSame(2, $bySection->total());

        $asLecturer = $this->repository->paginateAttendanceSessions($lecturer, []);
        $this->assertSame(2, $asLecturer->total());

        $ignoredStatus = $this->repository->paginateAttendanceSessions($admin, ['status' => 'not-a-status']);
        $this->assertSame(2, $ignoredStatus->total());
    }

    public function test_search_like_operator_pgsql_and_default(): void
    {
        $pgsql = new class extends AttendanceSessionRepository
        {
            protected function connectionDriverName(): string
            {
                return 'pgsql';
            }

            public function expose(): string
            {
                return $this->searchLikeOperator();
            }
        };

        $sqlite = new class extends AttendanceSessionRepository
        {
            protected function connectionDriverName(): string
            {
                return 'sqlite';
            }

            public function expose(): string
            {
                return $this->searchLikeOperator();
            }
        };

        $this->assertSame('ilike', $pgsql->expose());
        $this->assertSame('like', $sqlite->expose());

        // Real driver path (sqlite in phpunit).
        $real = new class extends AttendanceSessionRepository
        {
            public function exposeDriver(): string
            {
                return $this->connectionDriverName();
            }
        };
        $this->assertSame('sqlite', $real->exposeDriver());
    }
}
