<?php

namespace Tests\Feature\Web;

use App\Enums\SessionStatus;
use App\Enums\UserRole;
use App\Models\AttendanceSession;
use App\Models\CourseSection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AttendanceSessionWebTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_live_session_shell(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin, 'code' => 'ADMLIVEW']);
        $lecturer = User::factory()->lecturer()->create(['code' => 'GVLIVEW']);
        $section = CourseSection::query()->create([
            'code' => 'CT-LIVEW',
            'name' => 'Live Web',
            'semester' => '2026-1',
            'lecturer_id' => $lecturer->id,
        ]);
        $session = AttendanceSession::query()->create([
            'id' => (string) Str::uuid(),
            'course_section_id' => $section->id,
            'opened_by' => $lecturer->id,
            'title' => 'Live shell',
            'status' => SessionStatus::Open,
            'qr_token' => Str::random(40),
            'qr_expires_at' => now()->addHour(),
            'opens_at' => now(),
            'closes_at' => now()->addHour(),
            'late_after_minutes' => 15,
        ]);

        $this->actingAs($admin)
            ->get('/sessions/'.$session->id.'/live')
            ->assertOk()
            ->assertViewIs('sessions.live')
            ->assertViewHas('sessionId', $session->id);
    }

    public function test_public_check_in_shell_is_accessible(): void
    {
        $this->get('/check-in/demo-token-123')
            ->assertOk()
            ->assertViewIs('check-in.show')
            ->assertViewHas('qrToken', 'demo-token-123');
    }
}
