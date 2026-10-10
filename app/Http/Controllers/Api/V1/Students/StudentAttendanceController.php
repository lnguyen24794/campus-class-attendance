<?php

namespace App\Http\Controllers\Api\V1\Students;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Students\CheckInAttendanceRequest;
use App\Http\Requests\Api\V1\Students\IndexStudentAttendanceRequest;
use App\Http\Resources\Api\V1\Attendances\AttendanceResource;
use App\Http\Resources\Api\V1\Attendances\AttendanceResourceForList;
use App\Http\Response\ApiResponse;
use App\Services\AttendanceService;
use App\Support\AttendanceSuccessMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StudentAttendanceController extends Controller
{
    /**
     * @param  AttendanceService  $attendanceService  Attendance use-case service
     */
    public function __construct(
        private AttendanceService $attendanceService,
    ) {}

    /**
     * Display a listing of attendances for the authenticated student.
     *
     * @param  IndexStudentAttendanceRequest  $request  Validated list query
     */
    public function index(IndexStudentAttendanceRequest $request): AnonymousResourceCollection
    {
        $attendances = $this->attendanceService->fetchAttendanceList(
            $request->user(),
            $request->listFilters(),
        );

        return AttendanceResourceForList::collection($attendances);
    }

    /**
     * Store an attendance record via QR check-in (non-CRUD verb).
     *
     * @param  CheckInAttendanceRequest  $request  Validated check-in body
     */
    public function checkIn(CheckInAttendanceRequest $request): JsonResponse
    {
        $attendance = $this->attendanceService->createAttendance(
            $request->user(),
            $request->validated(),
        );

        return ApiResponse::created(
            new AttendanceResource($attendance),
            AttendanceSuccessMessage::from($attendance),
        );
    }
}
