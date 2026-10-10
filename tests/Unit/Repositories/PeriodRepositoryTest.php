<?php

namespace Tests\Unit\Repositories;

use App\Repositories\PeriodRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PeriodRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private PeriodRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(PeriodRepository::class);
    }

    public function test_find_list_search_insert_update_delete(): void
    {
        $period = $this->repository->insertPeriod([
            'number' => 1,
            'name' => 'Period One',
            'starts_at' => '07:00',
            'ends_at' => '07:50',
        ]);

        $this->assertSame($period->id, $this->repository->findPeriod($period->id)?->id);

        $this->repository->insertPeriod([
            'number' => 2,
            'name' => 'Period Two Search',
            'starts_at' => '08:00',
            'ends_at' => '08:50',
        ]);

        $this->assertCount(2, $this->repository->listPeriods());

        $search = $this->repository->searchPeriods(['q' => 'Search']);
        $this->assertSame(1, $search->total());

        $updated = $this->repository->updatePeriod($period, [
            'number' => 1,
            'name' => 'Period One Updated',
            'starts_at' => '07:05',
            'ends_at' => '07:55',
        ]);
        $this->assertSame('Period One Updated', $updated->name);

        $this->repository->deletePeriod($updated);
        $this->assertNull($this->repository->findPeriod($period->id));
    }
}
