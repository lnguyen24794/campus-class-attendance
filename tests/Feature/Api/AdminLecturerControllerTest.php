<?php

namespace Tests\Feature\Api;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminLecturerControllerTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create([
            'role' => UserRole::Admin,
            'code' => 'ADM010',
        ]);
    }

    public function test_admin_can_list_and_create_lecturers(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->getJson('/api/v1/admin/lecturers')
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->actingAs($admin)
            ->postJson('/api/v1/admin/lecturers', [
                'code' => 'GV100',
                'name' => 'Giảng viên AJAX',
                'email' => 'ajax.lecturer@cca.test',
                'password' => 'password',
            ])
            ->assertCreated()
            ->assertJsonPath('data.code', 'GV100')
            ->assertJsonPath('data.role', 'lecturer');

        $this->assertDatabaseHas('users', [
            'email' => 'ajax.lecturer@cca.test',
            'role' => 'lecturer',
        ]);
    }

    public function test_admin_can_search_and_paginate_lecturers(): void
    {
        $admin = $this->admin();

        foreach (range(1, 12) as $i) {
            User::factory()->lecturer()->create([
                'code' => sprintf('GV%03d', $i),
                'name' => $i === 2 ? 'Tran Thi Search' : "Lecturer {$i}",
                'email' => "lecturer{$i}@cca.test",
            ]);
        }

        $this->actingAs($admin)
            ->getJson('/api/v1/admin/lecturers?page=1&limit=5')
            ->assertOk()
            ->assertJsonPath('data.meta.per_page', 5)
            ->assertJsonCount(5, 'data.items');

        $this->actingAs($admin)
            ->getJson('/api/v1/admin/lecturers?q=Search')
            ->assertOk()
            ->assertJsonPath('data.meta.total', 1)
            ->assertJsonPath('data.items.0.name', 'Tran Thi Search');
    }

    public function test_admin_can_show_update_and_destroy_lecturer(): void
    {
        $admin = $this->admin();
        $lecturer = User::factory()->lecturer()->create([
            'code' => 'GV200',
            'name' => 'Old Lecturer',
            'email' => 'old.lecturer@cca.test',
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($admin)
            ->getJson("/api/v1/admin/lecturers/{$lecturer->id}")
            ->assertOk()
            ->assertJsonPath('data.code', 'GV200');

        $this->actingAs($admin)
            ->putJson("/api/v1/admin/lecturers/{$lecturer->id}", [
                'code' => 'GV201',
                'name' => 'New Lecturer',
                'email' => 'new.lecturer@cca.test',
            ])
            ->assertOk()
            ->assertJsonPath('data.code', 'GV201');

        $lecturer->refresh();
        $this->assertTrue(Hash::check('password', $lecturer->password));

        $this->actingAs($admin)
            ->putJson("/api/v1/admin/lecturers/{$lecturer->id}", [
                'code' => 'GV201',
                'name' => 'New Lecturer',
                'email' => 'new.lecturer@cca.test',
                'password' => 'secret99',
            ])
            ->assertOk();

        $lecturer->refresh();
        $this->assertTrue(Hash::check('secret99', $lecturer->password));

        $this->actingAs($admin)
            ->deleteJson("/api/v1/admin/lecturers/{$lecturer->id}")
            ->assertOk()
            ->assertJsonPath('message', 'Deleted successfully');

        $this->assertDatabaseMissing('users', ['id' => $lecturer->id]);
    }

    public function test_show_update_destroy_return_404_when_student_id_used_as_lecturer(): void
    {
        $admin = $this->admin();
        $student = User::factory()->student()->create(['code' => 'SV404']);

        $this->actingAs($admin)
            ->getJson("/api/v1/admin/lecturers/{$student->id}")
            ->assertNotFound();

        $this->actingAs($admin)
            ->putJson("/api/v1/admin/lecturers/{$student->id}", [
                'code' => 'SV404',
                'name' => 'Student',
                'email' => $student->email,
            ])
            ->assertNotFound();

        $this->actingAs($admin)
            ->deleteJson("/api/v1/admin/lecturers/{$student->id}")
            ->assertNotFound();
    }

    public function test_store_validation_returns_422(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->postJson('/api/v1/admin/lecturers', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['code', 'name', 'email', 'password']);
    }

    public function test_guest_receives_401(): void
    {
        $this->getJson('/api/v1/admin/lecturers')->assertUnauthorized();
    }

    public function test_student_role_receives_403(): void
    {
        $student = User::factory()->student()->create(['code' => 'SV403L']);

        $this->actingAs($student)
            ->getJson('/api/v1/admin/lecturers')
            ->assertForbidden();
    }
}
