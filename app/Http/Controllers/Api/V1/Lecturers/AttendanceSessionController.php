<?php

namespace App\Http\Controllers\Api\V1\Lecturers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Lecturers\StoreAttendanceSessionRequest;
use App\Http\Resources\Api\V1\AttendanceSessions\AttendanceSessionResource;
use App\Http\Response\ApiResponse;
use App\Services\AttendanceSessionService;
use Illuminate\Http\JsonResponse;

class AttendanceSessionController extends Controller
{
    /**
     * @param  AttendanceSessionService  $attendanceSessionService  Session use-case service
     */
    public function __construct(
        private AttendanceSessionService $attendanceSessionService,
    ) {}

    /**
     * Store (open) a classroom attendance session and return a short-lived QR token.
     *
     * @param  StoreAttendanceSessionRequest  $request  Validated session body
     */
    public function store(StoreAttendanceSessionRequest $request): JsonResponse
    {
        $session = $this->attendanceSessionService->createAttendanceSession(
            $request->user(),
            $request->validated(),
        );

        return ApiResponse::created(new AttendanceSessionResource($session));
    }
}
