<?php

namespace Tests\Unit\Repositories;

use App\Models\Subject;
use App\Models\User;
use App\Repositories\CourseSectionRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseSectionRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_find_and_insert_course_section(): void
    {
        $subject = Subject::query()->create([
            'code' => 'CSSEC',
            'name' => 'Course Section Subject',
            'credits' => 3,
        ]);
        $lecturer = User::factory()->lecturer()->create(['code' => 'GVSEC']);

        $repository = app(CourseSectionRepository::class);

        $section = $repository->insertCourseSection([
            'subject_id' => $subject->id,
            'code' => 'SEC01',
            'name' => 'Section 01',
            'semester' => '2026-1',
            'lecturer_id' => $lecturer->id,
        ]);

        $this->assertSame($section->id, $repository->findCourseSection($section->id)?->id);
        $this->assertNull($repository->findCourseSection('00000000-0000-0000-0000-000000000000'));
    }
}
