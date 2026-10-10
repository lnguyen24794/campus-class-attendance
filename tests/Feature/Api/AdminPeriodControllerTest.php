<?php

namespace Tests\Feature\Api;

use App\Enums\UserRole;
use App\Models\Period;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPeriodControllerTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create([
            'role' => UserRole::Admin,
            'code' => 'ADM030',
        ]);
    }

    public function test_admin_can_list_create_show_update_and_destroy_period(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->postJson('/api/v1/admin/periods', [
                'number' => 1,
                'name' => 'Tiết 1',
                'starts_at' => '07:00',
                'ends_at' => '07:50',
            ])
            ->assertCreated()
            ->assertJsonPath('data.number', 1)
            ->assertJsonPath('data.starts_at', '07:00');

        $period = Period::query()->where('number', 1)->firstOrFail();

        $this->actingAs($admin)
            ->getJson("/api/v1/admin/periods/{$period->id}")
            ->assertOk()
            ->assertJsonPath('data.name', 'Tiết 1');

        $this->actingAs($admin)
            ->putJson("/api/v1/admin/periods/{$period->id}", [
                'number' => 2,
                'name' => 'Tiết 2',
                'starts_at' => '08:00',
                'ends_at' => '08:50',
            ])
            ->assertOk()
            ->assertJsonPath('data.number', 2)
            ->assertJsonPath('data.name', 'Tiết 2');

        $this->actingAs($admin)
            ->deleteJson("/api/v1/admin/periods/{$period->id}")
            ->assertOk()
            ->assertJsonPath('message', 'Deleted successfully');

        $this->assertDatabaseMissing('periods', ['id' => $period->id]);
    }

    public function test_admin_can_search_periods(): void
    {
        $admin = $this->admin();

        Period::query()->create([
            'number' => 1,
            'name' => 'Morning A',
            'starts_at' => '07:00',
            'ends_at' => '07:50',
        ]);
        Period::query()->create([
            'number' => 2,
            'name' => 'Afternoon B',
            'starts_at' => '13:00',
            'ends_at' => '13:50',
        ]);

        $this->actingAs($admin)
            ->getJson('/api/v1/admin/periods?q=Afternoon')
            ->assertOk()
            ->assertJsonPath('data.meta.total', 1)
            ->assertJsonPath('data.items.0.name', 'Afternoon B');
    }

    public function test_store_validation_returns_422(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->postJson('/api/v1/admin/periods', [
                'number' => 1,
                'name' => 'Bad',
                'starts_at' => '10:00',
                'ends_at' => '09:00',
            ])
            ->assertUnprocessable();
    }

    public function test_guest_receives_401(): void
    {
        $this->getJson('/api/v1/admin/periods')->assertUnauthorized();
    }

    public function test_student_role_receives_403(): void
    {
        $student = User::factory()->student()->create(['code' => 'SV403P']);

        $this->actingAs($student)
            ->getJson('/api/v1/admin/periods')
            ->assertForbidden();
    }
}
