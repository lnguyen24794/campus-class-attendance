<?php

namespace App\Repositories;

use App\Models\CourseSection;

class CourseSectionRepository
{
    /**
     * Find a course section by id.
     *
     * @param  string  $id  Course section id
     */
    public function findCourseSection(string $id): ?CourseSection
    {
        return CourseSection::query()->find($id);
    }

    /**
     * Insert a new course section.
     *
     * @param  array<string, mixed>  $attributes  Column values
     */
    public function insertCourseSection(array $attributes): CourseSection
    {
        return CourseSection::query()->create($attributes);
    }
}
