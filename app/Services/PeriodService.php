<?php

namespace App\Services;

use App\Models\Period;
use App\Repositories\PeriodRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class PeriodService
{
    public function __construct(
        private PeriodRepository $periodRepository,
    ) {}

    /**
     * Fetch paginated period list.
     *
     * @param  array{q?: string, limit?: int, page?: int}  $params
     */
    public function fetchPeriodList(array $params = []): LengthAwarePaginator
    {
        return $this->periodRepository->searchPeriods($params);
    }

    /**
     * List all periods for select boxes.
     *
     * @return Collection<int, Period>
     */
    public function listAllPeriods(): Collection
    {
        return $this->periodRepository->listPeriods();
    }

    /**
     * Create a period.
     *
     * @param  array{number: int, name: string, starts_at: string, ends_at: string}  $params
     */
    public function createPeriod(array $params): Period
    {
        return $this->periodRepository->insertPeriod([
            'number' => (int) $params['number'],
            'name' => $params['name'],
            'starts_at' => $params['starts_at'],
            'ends_at' => $params['ends_at'],
        ]);
    }

    /**
     * Update a period.
     *
     * @param  array{number: int, name: string, starts_at: string, ends_at: string}  $params
     */
    public function updatePeriod(Period $period, array $params): Period
    {
        return $this->periodRepository->updatePeriod($period, [
            'number' => (int) $params['number'],
            'name' => $params['name'],
            'starts_at' => $params['starts_at'],
            'ends_at' => $params['ends_at'],
        ]);
    }

    /**
     * Delete a period.
     */
    public function deletePeriod(Period $period): void
    {
        $this->periodRepository->deletePeriod($period);
    }
}
