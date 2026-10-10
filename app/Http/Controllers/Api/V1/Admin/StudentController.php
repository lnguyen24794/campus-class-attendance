<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Students\IndexStudentRequest;
use App\Http\Requests\Api\V1\Students\StoreStudentRequest;
use App\Http\Requests\Api\V1\Students\UpdateStudentRequest;
use App\Http\Resources\Api\V1\Users\UserResource;
use App\Http\Resources\Api\V1\Users\UserResourceForList;
use App\Http\Response\ApiResponse;
use App\Models\User;
use App\Services\MasterUserService;
use App\Support\PaginationMeta;
use Illuminate\Http\JsonResponse;

class StudentController extends Controller
{
    public function __construct(
        private MasterUserService $masterUserService,
    ) {}

    /**
     * List students for admin CRUD (?q=&page=&limit=).
     */
    public function index(IndexStudentRequest $request): JsonResponse
    {
        $paginator = $this->masterUserService->fetchUserList(
            UserRole::Student,
            $request->listFilters(),
        );

        return ApiResponse::success([
            'items' => UserResourceForList::collection($paginator->items()),
            'meta' => PaginationMeta::from($paginator),
        ]);
    }

    /**
     * Create a student.
     */
    public function store(StoreStudentRequest $request): JsonResponse
    {
        $student = $this->masterUserService->createUser(UserRole::Student, $request->validated());

        return ApiResponse::created(new UserResource($student));
    }

    /**
     * Show a student.
     */
    public function show(User $student): JsonResponse
    {
        abort_unless($student->isStudent(), 404);

        return ApiResponse::success(new UserResource($student));
    }

    /**
     * Update a student.
     */
    public function update(UpdateStudentRequest $request, User $student): JsonResponse
    {
        abort_unless($student->isStudent(), 404);

        $student = $this->masterUserService->updateUser($student, UserRole::Student, $request->validated());

        return ApiResponse::success(new UserResource($student), 'Updated successfully');
    }

    /**
     * Delete a student.
     */
    public function destroy(User $student): JsonResponse
    {
        abort_unless($student->isStudent(), 404);

        $this->masterUserService->deleteUser($student, UserRole::Student);

        return ApiResponse::success(null, 'Deleted successfully');
    }
}
