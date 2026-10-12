<?php

namespace App\Services;

use App\Enums\SessionStatus;
use App\Models\AttendanceSession;
use App\Models\Period;
use App\Models\User;
use App\Repositories\AttendanceSessionRepository;
use App\Repositories\CourseSectionRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;

class AttendanceSessionService
{
    public function __construct(
        private CourseSectionRepository $courseSectionRepository,
        private AttendanceSessionRepository $attendanceSessionRepository,
    ) {}

    /**
     * Create an attendance session and issue a QR token.
     *
     * QR expiry is fixed to session closes_at (period end or duration) and
     * is NOT rolled forward when the token is refreshed.
     *
     * @param  User  $lecturer  Authenticated lecturer
     * @param  array{course_section_id: string, title: string, duration_minutes?: int, period_id?: string|null, late_after_minutes?: int}  $params
     *
     * @throws ModelNotFoundException
     */
    public function createAttendanceSession(User $lecturer, array $params): AttendanceSession
    {
        $courseSectionId = $params['course_section_id'];
        $title = $params['title'];
        $durationMinutes = (int) ($params['duration_minutes'] ?? 90);
        $lateAfterMinutes = (int) ($params['late_after_minutes'] ?? 15);

        $section = $this->courseSectionRepository->findCourseSection($courseSectionId);

        if (! $section || (int) $section->lecturer_id !== (int) $lecturer->id) {
            throw new ModelNotFoundException('Course section not found for lecturer.');
        }

        [$opensAt, $closesAt] = $this->resolveSessionWindow(
            $params['period_id'] ?? null,
            $durationMinutes,
        );

        return $this->attendanceSessionRepository->insertAttendanceSession([
            'course_section_id' => $section->id,
            'period_id' => $params['period_id'] ?? null,
            'opened_by' => $lecturer->id,
            'title' => $title,
            'status' => SessionStatus::Open,
            'qr_token' => Str::random(40),
            // Fixed to buổi/tiết — refresh QR must not change this.
            'qr_expires_at' => $closesAt,
            'opens_at' => $opensAt,
            'closes_at' => $closesAt,
            'late_after_minutes' => max(0, $lateAfterMinutes),
        ]);
    }

    /**
     * Rotate QR token only — keep qr_expires_at / closes_at unchanged.
     */
    public function refreshQrToken(AttendanceSession $session): AttendanceSession
    {
        $session->forceFill([
            'qr_token' => Str::random(40),
        ])->save();

        return $session->refresh();
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function resolveSessionWindow(?string $periodId, int $durationMinutes): array
    {
        $opensAt = now();

        if ($periodId) {
            $period = Period::query()->find($periodId);

            if ($period) {
                $today = $opensAt->toDateString();
                $periodStart = Carbon::parse($today.' '.$period->starts_at);
                $periodEnd = Carbon::parse($today.' '.$period->ends_at);

                // Keep open time as "now" if GV mở giữa tiết; close still follows tiết.
                if ($periodEnd->greaterThan($opensAt)) {
                    return [$opensAt, $periodEnd];
                }

                return [$periodStart, $periodEnd];
            }
        }

        return [$opensAt, $opensAt->copy()->addMinutes($durationMinutes)];
    }
}
