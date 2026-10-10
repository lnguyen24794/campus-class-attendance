<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\IndexAdminListRequest;
use App\Http\Requests\Api\V1\Admin\StoreMasterUserRequest;
use App\Http\Requests\Api\V1\Admin\UpdateMasterUserRequest;
use App\Http\Resources\Api\V1\Users\UserResource;
use App\Http\Resources\Api\V1\Users\UserResourceForList;
use App\Http\Response\ApiResponse;
use App\Models\User;
use App\Services\MasterUserService;
use App\Support\PaginationMeta;
use Illuminate\Http\JsonResponse;

class LecturerController extends Controller
{
    public function __construct(
        private MasterUserService $masterUserService,
    ) {}

    /**
     * List lecturers for admin CRUD (?q=&page=&limit=).
     */
    public function index(IndexAdminListRequest $request): JsonResponse
    {
        $paginator = $this->masterUserService->fetchUserList(
            UserRole::Lecturer,
            $request->listFilters(),
        );

        return ApiResponse::success([
            'items' => UserResourceForList::collection($paginator->items()),
            'meta' => PaginationMeta::from($paginator),
        ]);
    }

    /**
     * Create a lecturer.
     */
    public function store(StoreMasterUserRequest $request): JsonResponse
    {
        $lecturer = $this->masterUserService->createUser(UserRole::Lecturer, $request->validated());

        return ApiResponse::created(new UserResource($lecturer));
    }

    /**
     * Show a lecturer.
     */
    public function show(User $lecturer): JsonResponse
    {
        abort_unless($lecturer->isLecturer(), 404);

        return ApiResponse::success(new UserResource($lecturer));
    }

    /**
     * Update a lecturer.
     */
    public function update(UpdateMasterUserRequest $request, User $lecturer): JsonResponse
    {
        abort_unless($lecturer->isLecturer(), 404);

        $lecturer = $this->masterUserService->updateUser($lecturer, UserRole::Lecturer, $request->validated());

        return ApiResponse::success(new UserResource($lecturer), 'Updated successfully');
    }

    /**
     * Delete a lecturer.
     */
    public function destroy(User $lecturer): JsonResponse
    {
        abort_unless($lecturer->isLecturer(), 404);

        $this->masterUserService->deleteUser($lecturer, UserRole::Lecturer);

        return ApiResponse::success(null, 'Deleted successfully');
    }
}
