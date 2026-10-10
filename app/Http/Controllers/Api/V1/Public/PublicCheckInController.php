<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Exceptions\AttendanceException;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Attendances\AttendanceResource;
use App\Http\Response\ApiResponse;
use App\Repositories\AttendanceSessionRepository;
use App\Repositories\UserRepository;
use App\Services\AttendanceService;
use App\Support\AttendanceSuccessMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicCheckInController extends Controller
{
    public function __construct(
        private AttendanceSessionRepository $attendanceSessionRepository,
        private UserRepository $userRepository,
        private AttendanceService $attendanceService,
    ) {}

    /**
     * Session meta for the public check-in page (AJAX).
     */
    public function show(string $qrToken): JsonResponse
    {
        $session = $this->attendanceSessionRepository->findAttendanceSessionByQrToken($qrToken);

        return ApiResponse::success([
            'qr_token' => $qrToken,
            'expired' => ! $session || $session->isQrExpired() || ! $session->isOpen(),
            'session' => $session ? [
                'title' => $session->title,
                'course_section_code' => $session->courseSection?->code,
            ] : null,
        ]);
    }

    /**
     * Public check-in via AJAX (student code or email).
     */
    public function store(Request $request, string $qrToken): JsonResponse
    {
        $params = $request->validate([
            'identifier' => ['required', 'string', 'max:255'],
        ]);

        $student = $this->userRepository->findUserByEmailOrCode($params['identifier']);

        if (! $student || ! $student->isStudent()) {
            return ApiResponse::validationError(
                ['identifier' => ['Không tìm thấy sinh viên với mã/email này.']],
                'Validation failed',
            );
        }

        try {
            $attendance = $this->attendanceService->createAttendance($student, [
                'session_qr_token' => $qrToken,
            ]);
        } catch (AttendanceException $e) {
            return ApiResponse::error($e->getMessage(), 422);
        }

        return ApiResponse::created(
            new AttendanceResource($attendance),
            AttendanceSuccessMessage::from($attendance),
        );
    }
}
