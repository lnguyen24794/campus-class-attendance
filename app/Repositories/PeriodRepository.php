<?php

namespace App\Repositories;

use App\Models\Period;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class PeriodRepository
{
    /**
     * Find a period by id.
     */
    public function findPeriod(string $id): ?Period
    {
        return Period::query()->find($id);
    }

    /**
     * List all periods ordered by number.
     *
     * @return Collection<int, Period>
     */
    public function listPeriods(): Collection
    {
        return Period::query()->orderBy('number')->get();
    }

    /**
     * Search periods with pagination.
     *
     * @param  array{q?: string, limit?: int, page?: int}  $params
     */
    public function searchPeriods(array $params = []): LengthAwarePaginator
    {
        $limit = max(1, min((int) ($params['limit'] ?? 10), 100));
        $page = max(1, (int) ($params['page'] ?? 1));
        $q = trim((string) ($params['q'] ?? ''));

        return Period::query()
            ->when($q !== '', function ($query) use ($q): void {
                $query->where(function ($inner) use ($q): void {
                    $inner->where('name', 'like', "%{$q}%")
                        ->orWhere('number', 'like', "%{$q}%");
                });
            })
            ->orderBy('number')
            ->paginate($limit, ['*'], 'page', $page);
    }

    /**
     * Insert a period row.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function insertPeriod(array $attributes): Period
    {
        return Period::query()->create($attributes);
    }

    /**
     * Update a period row.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function updatePeriod(Period $period, array $attributes): Period
    {
        $period->fill($attributes);
        $period->save();

        return $period->refresh();
    }

    /**
     * Delete a period row.
     */
    public function deletePeriod(Period $period): void
    {
        $period->delete();
    }
}
