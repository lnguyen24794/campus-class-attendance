<?php

namespace Tests\Feature\Api;

use App\Enums\UserRole;
use App\Models\Period;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_returns_counts_and_empty_open_sessions(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
            'code' => 'ADM040',
        ]);

        User::factory()->student()->count(3)->create();
        User::factory()->lecturer()->count(2)->create();

        Subject::query()->create(['code' => 'S1', 'name' => 'Subject 1', 'credits' => 3]);
        Subject::query()->create(['code' => 'S2', 'name' => 'Subject 2', 'credits' => 3]);

        Period::query()->create([
            'number' => 1,
            'name' => 'P1',
            'starts_at' => '07:00',
            'ends_at' => '07:50',
        ]);

        $this->actingAs($admin)
            ->getJson('/api/v1/admin/dashboard')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.counts.students', 3)
            ->assertJsonPath('data.counts.lecturers', 2)
            ->assertJsonPath('data.counts.subjects', 2)
            ->assertJsonPath('data.counts.periods', 1)
            ->assertJsonPath('data.open_sessions', []);
    }

    public function test_guest_receives_401(): void
    {
        $this->getJson('/api/v1/admin/dashboard')->assertUnauthorized();
    }
}
