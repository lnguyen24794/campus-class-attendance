<?php

namespace App\Services;

use App\Models\Subject;
use App\Repositories\SubjectRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SubjectService
{
    public function __construct(
        private SubjectRepository $subjectRepository,
    ) {}

    /**
     * Fetch paginated subject list.
     *
     * @param  array{q?: string, limit?: int, page?: int}  $params
     */
    public function fetchSubjectList(array $params = []): LengthAwarePaginator
    {
        return $this->subjectRepository->searchSubjects($params);
    }

    /**
     * Create a subject.
     *
     * @param  array{code: string, name: string, credits?: int, description?: string|null}  $params
     */
    public function createSubject(array $params): Subject
    {
        return $this->subjectRepository->insertSubject([
            'code' => $params['code'],
            'name' => $params['name'],
            'credits' => (int) ($params['credits'] ?? 3),
            'description' => $params['description'] ?? null,
        ]);
    }

    /**
     * Update a subject.
     *
     * @param  array{code: string, name: string, credits?: int, description?: string|null}  $params
     */
    public function updateSubject(Subject $subject, array $params): Subject
    {
        return $this->subjectRepository->updateSubject($subject, [
            'code' => $params['code'],
            'name' => $params['name'],
            'credits' => (int) ($params['credits'] ?? $subject->credits),
            'description' => $params['description'] ?? null,
        ]);
    }

    /**
     * Delete a subject.
     */
    public function deleteSubject(Subject $subject): void
    {
        $this->subjectRepository->deleteSubject($subject);
    }
}
