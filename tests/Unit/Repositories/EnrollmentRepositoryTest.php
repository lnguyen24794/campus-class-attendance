<?php

namespace Tests\Unit\Repositories;

use App\Models\Subject;
use App\Models\User;
use App\Repositories\CourseSectionRepository;
use App\Repositories\EnrollmentRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnrollmentRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_exists_false_true_and_insert(): void
    {
        $subject = Subject::query()->create([
            'code' => 'ENR01',
            'name' => 'Enrollment Subject',
            'credits' => 3,
        ]);
        $lecturer = User::factory()->lecturer()->create(['code' => 'GVENR']);
        $student = User::factory()->student()->create(['code' => 'SVENR']);

        $section = app(CourseSectionRepository::class)->insertCourseSection([
            'subject_id' => $subject->id,
            'code' => 'ENRSEC',
            'name' => 'Enroll Section',
            'semester' => '2026-1',
            'lecturer_id' => $lecturer->id,
        ]);

        $repository = app(EnrollmentRepository::class);

        $this->assertFalse($repository->existsEnrollment($student->id, $section->id));

        $enrollment = $repository->insertEnrollment([
            'student_id' => $student->id,
            'course_section_id' => $section->id,
        ]);

        $this->assertNotNull($enrollment->id);
        $this->assertTrue($repository->existsEnrollment($student->id, $section->id));
    }
}
