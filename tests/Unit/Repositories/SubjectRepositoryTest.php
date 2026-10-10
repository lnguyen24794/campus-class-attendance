<?php

namespace Tests\Unit\Repositories;

use App\Repositories\SubjectRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubjectRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private SubjectRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(SubjectRepository::class);
    }

    public function test_find_search_insert_update_delete(): void
    {
        $subject = $this->repository->insertSubject([
            'code' => 'SUBR1',
            'name' => 'Repo Subject',
            'credits' => 3,
            'description' => null,
        ]);

        $this->assertSame($subject->id, $this->repository->findSubject($subject->id)?->id);
        $this->assertNull($this->repository->findSubject('00000000-0000-0000-0000-000000000000'));

        $this->repository->insertSubject([
            'code' => 'SUBR2',
            'name' => 'Other Search',
            'credits' => 2,
        ]);

        $found = $this->repository->searchSubjects(['q' => 'Search']);
        $this->assertSame(1, $found->total());

        $all = $this->repository->searchSubjects([]);
        $this->assertSame(2, $all->total());

        $updated = $this->repository->updateSubject($subject, ['name' => 'Updated Subject']);
        $this->assertSame('Updated Subject', $updated->name);

        $this->repository->deleteSubject($updated);
        $this->assertNull($this->repository->findSubject($subject->id));
    }
}
