<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Subjects\IndexSubjectRequest;
use App\Http\Requests\Api\V1\Subjects\StoreSubjectRequest;
use App\Http\Requests\Api\V1\Subjects\UpdateSubjectRequest;
use App\Http\Resources\Api\V1\Subjects\SubjectResource;
use App\Http\Resources\Api\V1\Subjects\SubjectResourceForList;
use App\Http\Response\ApiResponse;
use App\Models\Subject;
use App\Services\SubjectService;
use App\Support\PaginationMeta;
use Illuminate\Http\JsonResponse;

class SubjectController extends Controller
{
    public function __construct(
        private SubjectService $subjectService,
    ) {}

    /**
     * List subjects for admin CRUD (?q=&page=&limit=).
     */
    public function index(IndexSubjectRequest $request): JsonResponse
    {
        $paginator = $this->subjectService->fetchSubjectList($request->listFilters());

        return ApiResponse::success([
            'items' => SubjectResourceForList::collection($paginator->items()),
            'meta' => PaginationMeta::from($paginator),
        ]);
    }

    /**
     * Create a subject.
     */
    public function store(StoreSubjectRequest $request): JsonResponse
    {
        $subject = $this->subjectService->createSubject($request->validated());

        return ApiResponse::created(new SubjectResource($subject));
    }

    /**
     * Show a subject.
     */
    public function show(Subject $subject): JsonResponse
    {
        return ApiResponse::success(new SubjectResource($subject));
    }

    /**
     * Update a subject.
     */
    public function update(UpdateSubjectRequest $request, Subject $subject): JsonResponse
    {
        $subject = $this->subjectService->updateSubject($subject, $request->validated());

        return ApiResponse::success(new SubjectResource($subject), 'Updated successfully');
    }

    /**
     * Delete a subject.
     */
    public function destroy(Subject $subject): JsonResponse
    {
        $this->subjectService->deleteSubject($subject);

        return ApiResponse::success(null, 'Deleted successfully');
    }
}
