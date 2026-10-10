<?php

namespace Tests\Feature\Api;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminStudentControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_and_create_students_via_ajax_api(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
            'code' => 'ADM999',
        ]);

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
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
            'code' => 'ADM998',
        ]);

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
}
