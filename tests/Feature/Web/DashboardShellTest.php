<?php

namespace Tests\Feature\Web;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardShellTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_admin_can_view_dashboard_and_master_shells(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
            'code' => 'ADM200',
        ]);

        $this->actingAs($admin)->get('/dashboard')->assertOk()->assertViewIs('dashboard');
        $this->actingAs($admin)->get('/students')->assertOk()->assertViewIs('master.students.index');
        $this->actingAs($admin)->get('/lecturers')->assertOk()->assertViewIs('master.lecturers.index');
        $this->actingAs($admin)->get('/subjects')->assertOk()->assertViewIs('master.subjects.index');
        $this->actingAs($admin)->get('/periods')->assertOk()->assertViewIs('master.periods.index');
        $this->actingAs($admin)->get('/sessions')->assertOk()->assertViewIs('sessions.index');
        $this->actingAs($admin)->get('/sessions/create')->assertOk()->assertViewIs('sessions.create');
    }

    public function test_guest_is_redirected_from_master_shells(): void
    {
        foreach (['/dashboard', '/students', '/lecturers', '/subjects', '/periods', '/sessions', '/sessions/create'] as $uri) {
            $this->get($uri)->assertRedirect(route('login'));
        }
    }

    public function test_student_cannot_access_master_shells(): void
    {
        $student = User::factory()->student()->create(['code' => 'SVSHELL']);

        $this->actingAs($student)->get('/dashboard')->assertForbidden();
        $this->actingAs($student)->get('/students')->assertForbidden();
        $this->actingAs($student)->get('/sessions')->assertForbidden();
    }
}
