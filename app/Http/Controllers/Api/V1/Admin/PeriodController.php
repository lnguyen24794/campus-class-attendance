<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\IndexAdminListRequest;
use App\Http\Requests\Api\V1\Admin\StorePeriodRequest;
use App\Http\Requests\Api\V1\Admin\UpdatePeriodRequest;
use App\Http\Resources\Api\V1\Periods\PeriodResource;
use App\Http\Resources\Api\V1\Periods\PeriodResourceForList;
use App\Http\Response\ApiResponse;
use App\Models\Period;
use App\Services\PeriodService;
use App\Support\PaginationMeta;
use Illuminate\Http\JsonResponse;

class PeriodController extends Controller
{
    public function __construct(
        private PeriodService $periodService,
    ) {}

    /**
     * List periods for admin CRUD (?q=&page=&limit=).
     */
    public function index(IndexAdminListRequest $request): JsonResponse
    {
        $paginator = $this->periodService->fetchPeriodList($request->listFilters());

        return ApiResponse::success([
            'items' => PeriodResourceForList::collection($paginator->items()),
            'meta' => PaginationMeta::from($paginator),
        ]);
    }

    /**
     * Create a period.
     */
    public function store(StorePeriodRequest $request): JsonResponse
    {
        $period = $this->periodService->createPeriod($request->validated());

        return ApiResponse::created(new PeriodResource($period));
    }

    /**
     * Show a period.
     */
    public function show(Period $period): JsonResponse
    {
        return ApiResponse::success(new PeriodResource($period));
    }

    /**
     * Update a period.
     */
    public function update(UpdatePeriodRequest $request, Period $period): JsonResponse
    {
        $period = $this->periodService->updatePeriod($period, $request->validated());

        return ApiResponse::success(new PeriodResource($period), 'Updated successfully');
    }

    /**
     * Delete a period.
     */
    public function destroy(Period $period): JsonResponse
    {
        $this->periodService->deletePeriod($period);

        return ApiResponse::success(null, 'Deleted successfully');
    }
}
