<?php

namespace Tests\Unit\Services;

use App\Enums\UserRole;
use App\Models\User;
use App\Services\MasterUserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class MasterUserServiceTest extends TestCase
{
    use RefreshDatabase;

    private MasterUserService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(MasterUserService::class);
    }

    public function test_create_user_defaults_password(): void
    {
        $user = $this->service->createUser(UserRole::Student, [
            'name' => 'Default Pass',
            'email' => 'default.pass@cca.test',
            'code' => 'SVDEF',
        ]);

        $this->assertTrue($user->isStudent());
        $this->assertTrue(Hash::check('password', $user->password));
    }

    public function test_update_user_with_and_without_password(): void
    {
        $user = User::factory()->student()->create([
            'code' => 'SVUPD',
            'password' => Hash::make('password'),
        ]);

        $updated = $this->service->updateUser($user, UserRole::Student, [
            'name' => 'Updated',
            'email' => 'updated@cca.test',
            'code' => 'SVUPD2',
        ]);

        $this->assertSame('Updated', $updated->name);
        $this->assertTrue(Hash::check('password', $updated->password));

        $updated = $this->service->updateUser($updated, UserRole::Student, [
            'name' => 'Updated',
            'email' => 'updated@cca.test',
            'code' => 'SVUPD2',
            'password' => 'changed',
        ]);

        $this->assertTrue(Hash::check('changed', $updated->password));
    }

    public function test_update_and_delete_throw_on_role_mismatch(): void
    {
        $lecturer = User::factory()->lecturer()->create(['code' => 'GVMIS']);

        try {
            $this->service->updateUser($lecturer, UserRole::Student, [
                'name' => 'X',
                'email' => $lecturer->email,
                'code' => 'GVMIS',
            ]);
            $this->fail('Expected ValidationException on update');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('role', $e->errors());
        }

        try {
            $this->service->deleteUser($lecturer, UserRole::Student);
            $this->fail('Expected ValidationException on delete');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('role', $e->errors());
        }
    }

    public function test_fetch_user_list(): void
    {
        User::factory()->student()->count(2)->create();
        User::factory()->lecturer()->create(['code' => 'GVLIST']);

        $paginator = $this->service->fetchUserList(UserRole::Student, ['limit' => 10, 'page' => 1]);

        $this->assertSame(2, $paginator->total());
    }

    public function test_delete_user_with_matching_role(): void
    {
        $student = User::factory()->student()->create(['code' => 'SVDEL']);

        $this->service->deleteUser($student, UserRole::Student);

        $this->assertDatabaseMissing('users', ['id' => $student->id]);
    }
}
