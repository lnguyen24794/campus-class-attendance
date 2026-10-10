<?php

namespace Tests\Unit\Services;

use App\Services\PeriodService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PeriodServiceTest extends TestCase
{
    use RefreshDatabase;

    private PeriodService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(PeriodService::class);
    }

    public function test_crud_and_list_all_periods(): void
    {
        $period = $this->service->createPeriod([
            'number' => 1,
            'name' => 'Tiết 1',
            'starts_at' => '07:00',
            'ends_at' => '07:50',
        ]);

        $this->assertSame(1, $period->number);

        $updated = $this->service->updatePeriod($period, [
            'number' => 1,
            'name' => 'Tiết 1 cập nhật',
            'starts_at' => '07:10',
            'ends_at' => '08:00',
        ]);

        $this->assertSame('Tiết 1 cập nhật', $updated->name);

        $this->service->createPeriod([
            'number' => 2,
            'name' => 'Tiết 2',
            'starts_at' => '08:00',
            'ends_at' => '08:50',
        ]);

        $all = $this->service->listAllPeriods();
        $this->assertCount(2, $all);

        $paginator = $this->service->fetchPeriodList(['q' => 'cập nhật']);
        $this->assertSame(1, $paginator->total());

        $this->service->deletePeriod($updated);
        $this->assertDatabaseMissing('periods', ['id' => $updated->id]);
    }
}
