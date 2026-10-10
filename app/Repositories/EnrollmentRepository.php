<?php

namespace App\Repositories;

use App\Models\Enrollment;

class EnrollmentRepository
{
    /**
     * Check whether a student is enrolled in a course section.
     *
     * @param  int  $studentId  Student user id
     * @param  string  $courseSectionId  Course section id
     */
    public function existsEnrollment(int $studentId, string $courseSectionId): bool
    {
        return Enrollment::query()
            ->where('student_id', $studentId)
            ->where('course_section_id', $courseSectionId)
            ->exists();
    }

    /**
     * Insert a new enrollment.
     *
     * @param  array<string, mixed>  $attributes  Column values
     */
    public function insertEnrollment(array $attributes): Enrollment
    {
        return Enrollment::query()->create($attributes);
    }
}
