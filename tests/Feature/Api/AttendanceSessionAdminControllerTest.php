<?php

namespace Tests\Feature\Api;

use App\Enums\AttendanceStatus;
use App\Enums\SessionStatus;
use App\Enums\UserRole;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\CourseSection;
use App\Models\Period;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AttendanceSessionAdminControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_options_returns_sections_and_periods_for_admin(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin, 'code' => 'ADMOPT']);
        $lecturer = User::factory()->lecturer()->create(['code' => 'GVOPT']);
        $subject = Subject::query()->create(['code' => 'SUBOPT', 'name' => 'Opt Subject', 'credits' => 3]);
        CourseSection::query()->create([
            'subject_id' => $subject->id,
            'code' => 'SEC-OPT',
            'name' => 'Section Opt',
            'semester' => '2026-1',
            'lecturer_id' => $lecturer->id,
        ]);
        Period::query()->create([
            'number' => 1,
            'name' => 'Tiết 1',
            'starts_at' => '07:00:00',
            'ends_at' => '07:50:00',
        ]);
        Period::query()->create([
            'number' => 2,
            'name' => 'Bad window',
            'starts_at' => '10:00:00',
            'ends_at' => '09:00:00',
        ]);
        Period::query()->create([
            'number' => 3,
            'name' => 'Invalid times',
            'starts_at' => 'xx:yy',
            'ends_at' => 'zz:ww',
        ]);

        $this->actingAs($admin)
            ->getJson('/api/v1/admin/attendance-sessions/options')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.minutes_per_period', 45)
            ->assertJsonCount(1, 'data.sections')
            ->assertJsonCount(3, 'data.periods')
            ->assertJsonPath('data.periods.0.duration_minutes', 50)
            ->assertJsonPath('data.periods.1.duration_minutes', 45)
            ->assertJsonPath('data.periods.2.duration_minutes', 45);
    }

    public function test_options_filters_sections_for_lecturer(): void
    {
        $lecturer = User::factory()->lecturer()->create(['code' => 'GVOPT2']);
        $other = User::factory()->lecturer()->create(['code' => 'GVOPT3']);
        CourseSection::query()->create([
            'code' => 'MINE',
            'name' => 'Mine',
            'semester' => '2026-1',
            'lecturer_id' => $lecturer->id,
        ]);
        CourseSection::query()->create([
            'code' => 'THEIRS',
            'name' => 'Theirs',
            'semester' => '2026-1',
            'lecturer_id' => $other->id,
        ]);

        $this->actingAs($lecturer)
            ->getJson('/api/v1/admin/attendance-sessions/options')
            ->assertOk()
            ->assertJsonCount(1, 'data.sections')
            ->assertJsonPath('data.sections.0.label', 'MINE — Mine');
    }

    public function test_index_lists_sessions_with_filters(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin, 'code' => 'ADMLIST']);
        $lecturer = User::factory()->lecturer()->create(['code' => 'GVLIST']);
        $section = CourseSection::query()->create([
            'code' => 'CT101-01',
            'name' => 'Lập trình Web',
            'semester' => '2026-1',
            'lecturer_id' => $lecturer->id,
        ]);
        $period = Period::query()->create([
            'number' => 1,
            'name' => 'Tiết 1',
            'starts_at' => '07:00',
            'ends_at' => '07:50',
        ]);
        $session = AttendanceSession::query()->create([
            'id' => (string) Str::uuid(),
            'course_section_id' => $section->id,
            'period_id' => $period->id,
            'opened_by' => $lecturer->id,
            'title' => 'Buổi Laravel',
            'status' => SessionStatus::Open,
            'qr_token' => Str::random(40),
            'qr_expires_at' => now()->addHour(),
            'opens_at' => now()->subMinutes(5),
            'closes_at' => now()->addHour(),
            'late_after_minutes' => 15,
        ]);
        Attendance::query()->create([
            'id' => (string) Str::uuid(),
            'student_id' => User::factory()->student()->create()->id,
            'attendance_session_id' => $session->id,
            'course_section_id' => $section->id,
            'status' => AttendanceStatus::Present,
            'checked_in_at' => now(),
            'unique_key' => 'att_admin_list_'.$session->id,
        ]);

        $this->actingAs($admin)
            ->getJson('/api/v1/admin/attendance-sessions?q=Laravel&status=open&page=1&limit=10')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.title', 'Buổi Laravel')
            ->assertJsonPath('data.items.0.course_section_code', 'CT101-01')
            ->assertJsonPath('data.items.0.attendances_count', 1)
            ->assertJsonPath('data.meta.total', 1);
    }

    public function test_lecturer_index_only_sees_own_sessions(): void
    {
        $lecturer = User::factory()->lecturer()->create(['code' => 'GVOWN']);
        $other = User::factory()->lecturer()->create(['code' => 'GVOTH']);
        $mine = CourseSection::query()->create([
            'code' => 'MINE-1',
            'name' => 'Mine',
            'semester' => '2026-1',
            'lecturer_id' => $lecturer->id,
        ]);
        $theirs = CourseSection::query()->create([
            'code' => 'OTH-1',
            'name' => 'Other',
            'semester' => '2026-1',
            'lecturer_id' => $other->id,
        ]);

        foreach ([[$mine, $lecturer, 'Mine Session'], [$theirs, $other, 'Other Session']] as [$section, $opener, $title]) {
            AttendanceSession::query()->create([
                'id' => (string) Str::uuid(),
                'course_section_id' => $section->id,
                'opened_by' => $opener->id,
                'title' => $title,
                'status' => SessionStatus::Open,
                'qr_token' => Str::random(40),
                'qr_expires_at' => now()->addHour(),
                'opens_at' => now(),
                'closes_at' => now()->addHour(),
                'late_after_minutes' => 15,
            ]);
        }

        $this->actingAs($lecturer)
            ->getJson('/api/v1/admin/attendance-sessions')
            ->assertOk()
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.title', 'Mine Session');
    }

    public function test_admin_can_store_session_for_section_lecturer(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin, 'code' => 'ADMSTORE']);
        $lecturer = User::factory()->lecturer()->create(['code' => 'GVSTORE']);
        $section = CourseSection::query()->create([
            'code' => 'CT-STORE',
            'name' => 'Store Section',
            'semester' => '2026-1',
            'lecturer_id' => $lecturer->id,
        ]);

        $this->actingAs($admin)
            ->postJson('/api/v1/admin/attendance-sessions', [
                'course_section_id' => $section->id,
                'title' => 'Buổi admin mở',
                'duration_minutes' => 60,
                'late_after_minutes' => 10,
            ])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.session.title', 'Buổi admin mở')
            ->assertJsonStructure(['data' => ['session', 'live_url']]);

        $this->assertDatabaseHas('attendance_sessions', [
            'course_section_id' => $section->id,
            'opened_by' => $lecturer->id,
            'title' => 'Buổi admin mở',
        ]);
    }

    public function test_live_and_refresh_qr_payload(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin, 'code' => 'ADMLIVE']);
        $lecturer = User::factory()->lecturer()->create(['code' => 'GVLIVE']);
        $student = User::factory()->student()->create(['code' => 'SVLIVE']);
        $section = CourseSection::query()->create([
            'code' => 'CT-LIVE',
            'name' => 'Live Section',
            'semester' => '2026-1',
            'lecturer_id' => $lecturer->id,
        ]);
        $expiresAt = now()->addHour()->startOfSecond();
        $session = AttendanceSession::query()->create([
            'id' => (string) Str::uuid(),
            'course_section_id' => $section->id,
            'opened_by' => $lecturer->id,
            'title' => 'Live Buổi',
            'status' => SessionStatus::Open,
            'qr_token' => 'old-token-'.Str::random(8),
            'qr_expires_at' => $expiresAt,
            'opens_at' => now()->subMinutes(5),
            'closes_at' => $expiresAt,
            'late_after_minutes' => 15,
        ]);
        Attendance::query()->create([
            'id' => (string) Str::uuid(),
            'student_id' => $student->id,
            'attendance_session_id' => $session->id,
            'course_section_id' => $section->id,
            'status' => AttendanceStatus::Present,
            'checked_in_at' => now(),
            'unique_key' => 'att_live_'.$session->id,
        ]);

        $live = $this->actingAs($admin)
            ->getJson('/api/v1/admin/attendance-sessions/'.$session->id.'/live')
            ->assertOk()
            ->assertJsonPath('data.session.title', 'Live Buổi')
            ->assertJsonPath('data.attendances.0.student_code', 'SVLIVE')
            ->assertJsonStructure(['data' => ['check_in_url', 'qr_svg', 'attendances']]);

        $oldToken = $session->qr_token;
        $refresh = $this->actingAs($admin)
            ->postJson('/api/v1/admin/attendance-sessions/'.$session->id.'/refresh-qr')
            ->assertOk();

        $session->refresh();
        $this->assertNotSame($oldToken, $session->qr_token);
        $this->assertTrue($expiresAt->equalTo($session->qr_expires_at));
        $this->assertStringContainsString($session->qr_token, $refresh->json('data.check_in_url'));
        $this->assertNotEmpty($live->json('data.qr_svg'));
    }
}
