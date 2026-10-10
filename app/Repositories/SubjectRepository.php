<?php

namespace App\Repositories;

use App\Models\Subject;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SubjectRepository
{
    /**
     * Find a subject by id.
     */
    public function findSubject(string $id): ?Subject
    {
        return Subject::query()->find($id);
    }

    /**
     * Search subjects with pagination.
     *
     * @param  array{q?: string, limit?: int, page?: int}  $params
     */
    public function searchSubjects(array $params = []): LengthAwarePaginator
    {
        $limit = max(1, min((int) ($params['limit'] ?? 10), 100));
        $page = max(1, (int) ($params['page'] ?? 1));
        $q = trim((string) ($params['q'] ?? ''));

        return Subject::query()
            ->when($q !== '', function ($query) use ($q): void {
                $query->where(function ($inner) use ($q): void {
                    $inner->where('code', 'like', "%{$q}%")
                        ->orWhere('name', 'like', "%{$q}%");
                });
            })
            ->orderBy('code')
            ->paginate($limit, ['*'], 'page', $page);
    }

    /**
     * Insert a subject row.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function insertSubject(array $attributes): Subject
    {
        return Subject::query()->create($attributes);
    }

    /**
     * Update a subject row.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function updateSubject(Subject $subject, array $attributes): Subject
    {
        $subject->fill($attributes);
        $subject->save();

        return $subject->refresh();
    }

    /**
     * Delete a subject row.
     */
    public function deleteSubject(Subject $subject): void
    {
        $subject->delete();
    }
}
