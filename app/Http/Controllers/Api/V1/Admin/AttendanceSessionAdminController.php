<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\IndexAttendanceSessionRequest;
use App\Http\Requests\Api\V1\Lecturers\StoreAttendanceSessionRequest;
use App\Http\Resources\Api\V1\AttendanceSessions\AttendanceSessionResource;
use App\Http\Response\ApiResponse;
use App\Models\AttendanceSession;
use App\Models\CourseSection;
use App\Models\Period;
use App\Repositories\AttendanceRepository;
use App\Repositories\AttendanceSessionRepository;
use App\Services\AttendanceSessionService;
use App\Services\PublicUrlService;
use App\Support\PaginationMeta;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Throwable;

class AttendanceSessionAdminController extends Controller
{
    public function __construct(
        private AttendanceSessionService $attendanceSessionService,
        private AttendanceSessionRepository $attendanceSessionRepository,
        private AttendanceRepository $attendanceRepository,
        private PublicUrlService $publicUrlService,
    ) {}

    /**
     * List attendance session history (AJAX) (?q=&status=&page=&limit=).
     */
    public function index(IndexAttendanceSessionRequest $request): JsonResponse
    {
        $paginator = $this->attendanceSessionRepository->paginateAttendanceSessions(
            $request->user(),
            $request->listFilters(),
        );

        $items = collect($paginator->items())->map(fn (AttendanceSession $session) => [
            'id' => $session->id,
            'title' => $session->title,
            'course_section_code' => $session->courseSection?->code,
            'course_section_name' => $session->courseSection?->name,
            'period_name' => $session->period?->name,
            'opened_by_name' => $session->openedBy?->name,
            'status' => $session->status?->value,
            'status_label' => $session->status?->label(),
            'opens_at' => $session->opens_at?->toAtomString(),
            'closes_at' => $session->closes_at?->toAtomString(),
            'attendances_count' => (int) $session->attendances_count,
            'live_url' => route('sessions.live', $session),
        ]);

        return ApiResponse::success([
            'items' => $items,
            'meta' => PaginationMeta::from($paginator),
        ]);
    }

    /**
     * Open an attendance session (AJAX from admin UI).
     */
    public function store(StoreAttendanceSessionRequest $request): JsonResponse
    {
        $params = $request->validated();
        $lecturer = $request->user();

        if ($lecturer->isAdmin()) {
            $section = CourseSection::query()->findOrFail($params['course_section_id']);
            $lecturer = $section->lecturer;
        }

        $session = $this->attendanceSessionService->createAttendanceSession($lecturer, $params);

        return ApiResponse::created([
            'session' => new AttendanceSessionResource($session),
            'live_url' => route('sessions.live', $session),
        ]);
    }

    /**
     * Live payload: session meta, QR SVG, current attendances.
     */
    public function live(AttendanceSession $session): JsonResponse
    {
        $session->load(['courseSection.subject', 'period', 'openedBy']);
        $checkInUrl = $this->publicUrlService->checkInUrl($session->qr_token);
        $qrSvg = (string) QrCode::format('svg')->size(280)->margin(1)->generate($checkInUrl);

        return ApiResponse::success([
            'session' => [
                'id' => $session->id,
                'title' => $session->title,
                'course_section_code' => $session->courseSection?->code,
                'period_name' => $session->period?->name,
                'qr_expires_at' => $session->qr_expires_at?->toAtomString(),
                'opens_at' => $session->opens_at?->toAtomString(),
                'closes_at' => $session->closes_at?->toAtomString(),
                'late_after_minutes' => (int) $session->late_after_minutes,
                'status' => $session->status?->value,
                'status_label' => $session->status?->label(),
            ],
            'check_in_url' => $checkInUrl,
            'qr_svg' => $qrSvg,
            'attendances' => $this->attendanceRepository
                ->listAttendancesBySession($session->id)
                ->map(fn ($row) => [
                    'id' => $row->id,
                    'student_code' => $row->student?->code,
                    'student_name' => $row->student?->name,
                    'status' => $row->status?->value,
                    'status_label' => $row->status?->label(),
                    'checked_in_at' => $row->checked_in_at?->toAtomString(),
                ])
                ->values(),
        ]);
    }

    /**
     * Refresh QR token via AJAX.
     */
    public function refreshQr(AttendanceSession $session): JsonResponse
    {
        $session = $this->attendanceSessionService->refreshQrToken($session);

        return $this->live($session);
    }

    /**
     * Options for opening a session (sections + periods).
     */
    public function options(Request $request): JsonResponse
    {
        $user = $request->user();

        $sections = CourseSection::query()
            ->with('subject')
            ->when($user->isLecturer(), fn ($q) => $q->where('lecturer_id', $user->id))
            ->orderBy('code')
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'label' => $s->code.' — '.$s->name.($s->subject ? ' ('.$s->subject->code.')' : ''),
            ]);

        $periods = Period::query()
            ->orderBy('number')
            ->get()
            ->map(function (Period $p) {
                $startsAt = substr((string) $p->starts_at, 0, 5);
                $endsAt = substr((string) $p->ends_at, 0, 5);
                $duration = $this->periodDurationMinutes((string) $p->starts_at, (string) $p->ends_at);

                return [
                    'id' => $p->id,
                    'label' => 'Tiết '.$p->number.' — '.$p->name.' ('.$startsAt.'–'.$endsAt.')',
                    'number' => (int) $p->number,
                    'starts_at' => $startsAt,
                    'ends_at' => $endsAt,
                    'duration_minutes' => $duration,
                ];
            });

        return ApiResponse::success([
            'sections' => $sections,
            'periods' => $periods,
            'minutes_per_period' => 45,
        ]);
    }

    /**
     * Duration in minutes from period start/end (H:i or H:i:s).
     */
    private function periodDurationMinutes(string $startsAt, string $endsAt): int
    {
        try {
            $start = Carbon::createFromFormat('H:i:s', strlen($startsAt) === 5 ? $startsAt.':00' : $startsAt);
            $end = Carbon::createFromFormat('H:i:s', strlen($endsAt) === 5 ? $endsAt.':00' : $endsAt);
        } catch (Throwable) {
            return 45;
        }

        $minutes = (int) $start->diffInMinutes($end, false);

        return $minutes > 0 ? $minutes : 45;
    }
}
