<?php

namespace Tests\Feature\Api;

use App\Enums\UserRole;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSubjectControllerTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create([
            'role' => UserRole::Admin,
            'code' => 'ADM020',
        ]);
    }

    public function test_admin_can_list_create_show_update_and_destroy_subject(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->postJson('/api/v1/admin/subjects', [
                'code' => 'CS101',
                'name' => 'Lập trình 1',
                'credits' => 3,
                'description' => 'Intro',
            ])
            ->assertCreated()
            ->assertJsonPath('data.code', 'CS101')
            ->assertJsonPath('data.credits', 3);

        $subject = Subject::query()->where('code', 'CS101')->firstOrFail();

        $this->actingAs($admin)
            ->getJson("/api/v1/admin/subjects/{$subject->id}")
            ->assertOk()
            ->assertJsonPath('data.name', 'Lập trình 1');

        $this->actingAs($admin)
            ->putJson("/api/v1/admin/subjects/{$subject->id}", [
                'code' => 'CS102',
                'name' => 'Lập trình 2',
                'credits' => 4,
                'description' => 'Updated',
            ])
            ->assertOk()
            ->assertJsonPath('data.code', 'CS102')
            ->assertJsonPath('data.credits', 4);

        $this->actingAs($admin)
            ->deleteJson("/api/v1/admin/subjects/{$subject->id}")
            ->assertOk()
            ->assertJsonPath('message', 'Deleted successfully');

        $this->assertDatabaseMissing('subjects', ['id' => $subject->id]);
    }

    public function test_admin_can_search_and_paginate_subjects(): void
    {
        $admin = $this->admin();

        foreach (range(1, 12) as $i) {
            Subject::query()->create([
                'code' => sprintf('SUB%03d', $i),
                'name' => $i === 4 ? 'Database Search' : "Subject {$i}",
                'credits' => 3,
            ]);
        }

        $this->actingAs($admin)
            ->getJson('/api/v1/admin/subjects?page=1&limit=5')
            ->assertOk()
            ->assertJsonPath('data.meta.per_page', 5)
            ->assertJsonCount(5, 'data.items');

        $this->actingAs($admin)
            ->getJson('/api/v1/admin/subjects?q=Search')
            ->assertOk()
            ->assertJsonPath('data.meta.total', 1)
            ->assertJsonPath('data.items.0.name', 'Database Search');
    }

    public function test_store_validation_returns_422(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->postJson('/api/v1/admin/subjects', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['code', 'name', 'credits']);
    }

    public function test_guest_receives_401(): void
    {
        $this->getJson('/api/v1/admin/subjects')->assertUnauthorized();
    }

    public function test_student_role_receives_403(): void
    {
        $student = User::factory()->student()->create(['code' => 'SV403S']);

        $this->actingAs($student)
            ->getJson('/api/v1/admin/subjects')
            ->assertForbidden();
    }
}
