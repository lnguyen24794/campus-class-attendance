<?php

namespace App\Services;

use App\Enums\AttendanceStatus;
use App\Events\AttendanceCheckedIn;
use App\Exceptions\AttendanceException;
use App\Models\Attendance;
use App\Models\User;
use App\Repositories\AttendanceRepository;
use App\Repositories\AttendanceSessionRepository;
use App\Repositories\EnrollmentRepository;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    public function __construct(
        private AttendanceRepository $attendanceRepository,
        private AttendanceSessionRepository $attendanceSessionRepository,
        private EnrollmentRepository $enrollmentRepository,
    ) {}

    /**
     * Fetch paginated attendance list for the authenticated student.
     *
     * @param  User  $student  Authenticated student
     * @param  array{q?: string, limit?: int, page?: int}  $params  Validated query parameters
     */
    public function fetchAttendanceList(User $student, array $params): LengthAwarePaginator
    {
        return $this->attendanceRepository->searchAttendances($student->id, $params);
    }

    /**
     * Create an attendance record via QR check-in.
     *
     * @param  User  $student  Authenticated student
     * @param  array{session_qr_token: string}  $params  Validated body parameters
     *
     * @throws AttendanceException
     */
    public function createAttendance(User $student, array $params): Attendance
    {
        $sessionQrToken = $params['session_qr_token'];
        $session = $this->attendanceSessionRepository->findAttendanceSessionByQrToken($sessionQrToken);

        if (! $session) {
            throw AttendanceException::qrExpired();
        }

        if ($session->isQrExpired()) {
            throw AttendanceException::qrExpired();
        }

        if (! $session->isOpen()) {
            throw AttendanceException::sessionNotOpen();
        }

        if (! $this->enrollmentRepository->existsEnrollment(
            $student->id,
            $session->course_section_id,
        )) {
            throw AttendanceException::notEnrolled();
        }

        if ($this->attendanceRepository->findAttendanceByStudentAndSession($student->id, $session->id)) {
            throw AttendanceException::alreadyCheckedIn();
        }

        $status = $this->resolveStatus($session->opens_at, $session->late_after_minutes);

        return DB::transaction(function () use ($student, $session, $status): Attendance {
            $attendance = $this->attendanceRepository->insertAttendance([
                'student_id' => $student->id,
                'attendance_session_id' => $session->id,
                'course_section_id' => $session->course_section_id,
                'status' => $status,
                'checked_in_at' => now(),
                'unique_key' => 'att_'.$student->id.'_'.$session->id,
            ]);

            $attendance->load([
                'courseSection.subject',
                'attendanceSession.period',
                'student',
            ]);

            event(new AttendanceCheckedIn($attendance));

            return $attendance;
        });
    }

    /**
     * Resolve present/late status from session open time and grace window.
     */
    private function resolveStatus(CarbonInterface $opensAt, int $lateAfterMinutes): AttendanceStatus
    {
        if (now()->greaterThan($opensAt->copy()->addMinutes($lateAfterMinutes))) {
            return AttendanceStatus::Late;
        }

        return AttendanceStatus::Present;
    }
}
