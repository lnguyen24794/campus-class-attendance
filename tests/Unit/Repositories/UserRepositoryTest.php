<?php

namespace Tests\Unit\Repositories;

use App\Enums\UserRole;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private UserRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(UserRepository::class);
    }

    public function test_find_user_and_find_by_email_or_code(): void
    {
        $user = User::factory()->student()->create([
            'code' => 'SVFIND',
            'email' => 'find@cca.test',
        ]);

        $this->assertSame($user->id, $this->repository->findUser($user->id)?->id);
        $this->assertNull($this->repository->findUser(999999));

        $this->assertSame($user->id, $this->repository->findUserByEmailOrCode('find@cca.test')?->id);
        $this->assertSame($user->id, $this->repository->findUserByEmailOrCode('SVFIND')?->id);
        $this->assertNull($this->repository->findUserByEmailOrCode('missing'));
    }

    public function test_search_with_and_without_q(): void
    {
        User::factory()->student()->create(['code' => 'SVA', 'name' => 'Alpha', 'email' => 'a@cca.test']);
        User::factory()->student()->create(['code' => 'SVB', 'name' => 'Beta Search', 'email' => 'b@cca.test']);
        User::factory()->lecturer()->create(['code' => 'GVA', 'name' => 'Gamma', 'email' => 'g@cca.test']);

        $allStudents = $this->repository->searchUsersByRole(UserRole::Student, ['limit' => 10, 'page' => 1]);
        $this->assertSame(2, $allStudents->total());

        $filtered = $this->repository->searchUsersByRole(UserRole::Student, ['q' => 'Search']);
        $this->assertSame(1, $filtered->total());
    }

    public function test_insert_update_delete(): void
    {
        $user = $this->repository->insertUser([
            'name' => 'Repo User',
            'email' => 'repo@cca.test',
            'code' => 'SVREPO',
            'password' => 'password',
            'role' => UserRole::Student,
        ]);

        $this->assertDatabaseHas('users', ['email' => 'repo@cca.test']);

        $updated = $this->repository->updateUser($user, ['name' => 'Repo Updated']);
        $this->assertSame('Repo Updated', $updated->name);

        $this->repository->deleteUser($updated);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}
