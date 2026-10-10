<?php

namespace Tests\Unit\Services;

use App\Models\Subject;
use App\Services\SubjectService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubjectServiceTest extends TestCase
{
    use RefreshDatabase;

    private SubjectService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(SubjectService::class);
    }

    public function test_create_defaults_credits_to_three(): void
    {
        $subject = $this->service->createSubject([
            'code' => 'CS001',
            'name' => 'Intro',
        ]);

        $this->assertSame(3, $subject->credits);
        $this->assertNull($subject->description);
    }

    public function test_update_defaults_and_delete(): void
    {
        $subject = $this->service->createSubject([
            'code' => 'CS002',
            'name' => 'Algo',
            'credits' => 4,
            'description' => 'Desc',
        ]);

        $updated = $this->service->updateSubject($subject, [
            'code' => 'CS002B',
            'name' => 'Algo 2',
        ]);

        $this->assertSame(4, $updated->credits);
        $this->assertNull($updated->description);

        $this->service->deleteSubject($updated);

        $this->assertDatabaseMissing('subjects', ['id' => $updated->id]);
    }

    public function test_fetch_subject_list(): void
    {
        Subject::query()->create(['code' => 'A1', 'name' => 'A', 'credits' => 3]);
        Subject::query()->create(['code' => 'B1', 'name' => 'B Search', 'credits' => 3]);

        $paginator = $this->service->fetchSubjectList(['q' => 'Search', 'limit' => 10, 'page' => 1]);

        $this->assertSame(1, $paginator->total());
    }
}
