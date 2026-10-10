<?php

namespace Tests\Feature\Api;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminStudentControllerTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create([
            'role' => UserRole::Admin,
            'code' => 'ADM001',
        ]);
    }

    public function test_admin_can_list_and_create_students_via_ajax_api(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->withSession([])
            ->getJson('/api/v1/admin/students')
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->actingAs($admin)
            ->postJson('/api/v1/admin/students', [
                'code' => 'SV100',
                'name' => 'Sinh viên AJAX',
                'email' => 'ajax.student@cca.test',
                'password' => 'password',
            ])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.code', 'SV100');

        $this->assertDatabaseHas('users', [
            'email' => 'ajax.student@cca.test',
            'role' => 'student',
        ]);
    }

    public function test_admin_can_search_and_paginate_students(): void
    {
        $admin = $this->admin();

        foreach (range(1, 16) as $i) {
            User::factory()->create([
                'role' => UserRole::Student,
                'code' => sprintf('SV%03d', $i),
                'name' => $i === 3 ? 'Nguyen Van Search' : "Student {$i}",
                'email' => "student{$i}@cca.test",
            ]);
        }

        $this->actingAs($admin)
            ->withSession([])
            ->getJson('/api/v1/admin/students?page=1&limit=10')
            ->assertOk()
            ->assertJsonPath('data.meta.per_page', 10)
            ->assertJsonPath('data.meta.current_page', 1)
            ->assertJsonPath('data.meta.last_page', 2)
            ->assertJsonCount(10, 'data.items');

        $this->actingAs($admin)
            ->withSession([])
            ->getJson('/api/v1/admin/students?q=Search&page=1&limit=10')
            ->assertOk()
            ->assertJsonPath('data.meta.total', 1)
            ->assertJsonPath('data.items.0.name', 'Nguyen Van Search');
    }

    public function test_admin_can_show_update_and_destroy_student(): void
    {
        $admin = $this->admin();
        $student = User::factory()->student()->create([
            'code' => 'SV200',
            'name' => 'Old Name',
            'email' => 'old.student@cca.test',
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($admin)
            ->getJson("/api/v1/admin/students/{$student->id}")
            ->assertOk()
            ->assertJsonPath('data.code', 'SV200')
            ->assertJsonPath('data.email', 'old.student@cca.test');

        $this->actingAs($admin)
            ->putJson("/api/v1/admin/students/{$student->id}", [
                'code' => 'SV201',
                'name' => 'New Name',
                'email' => 'new.student@cca.test',
            ])
            ->assertOk()
            ->assertJsonPath('data.code', 'SV201')
            ->assertJsonPath('data.name', 'New Name');

        $student->refresh();
        $this->assertTrue(Hash::check('password', $student->password));

        $this->actingAs($admin)
            ->putJson("/api/v1/admin/students/{$student->id}", [
                'code' => 'SV201',
                'name' => 'New Name',
                'email' => 'new.student@cca.test',
                'password' => 'new-password',
            ])
            ->assertOk();

        $student->refresh();
        $this->assertTrue(Hash::check('new-password', $student->password));

        $this->actingAs($admin)
            ->deleteJson("/api/v1/admin/students/{$student->id}")
            ->assertOk()
            ->assertJsonPath('message', 'Deleted successfully');

        $this->assertDatabaseMissing('users', ['id' => $student->id]);
    }

    public function test_show_update_destroy_return_404_when_lecturer_id_used_as_student(): void
    {
        $admin = $this->admin();
        $lecturer = User::factory()->lecturer()->create(['code' => 'GV404']);

        $this->actingAs($admin)
            ->getJson("/api/v1/admin/students/{$lecturer->id}")
            ->assertNotFound();

        $this->actingAs($admin)
            ->putJson("/api/v1/admin/students/{$lecturer->id}", [
                'code' => 'GV404',
                'name' => 'Lecturer',
                'email' => $lecturer->email,
            ])
            ->assertNotFound();

        $this->actingAs($admin)
            ->deleteJson("/api/v1/admin/students/{$lecturer->id}")
            ->assertNotFound();
    }

    public function test_store_validation_returns_422(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->postJson('/api/v1/admin/students', [
                'code' => '',
                'name' => '',
                'email' => 'not-an-email',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['code', 'name', 'email', 'password']);
    }

    public function test_guest_receives_401(): void
    {
        $this->getJson('/api/v1/admin/students')->assertUnauthorized();
    }

    public function test_student_role_receives_403(): void
    {
        $student = User::factory()->student()->create(['code' => 'SV403']);

        $this->actingAs($student)
            ->getJson('/api/v1/admin/students')
            ->assertForbidden()
            ->assertJsonPath('message', 'Forbidden.');
    }
}
