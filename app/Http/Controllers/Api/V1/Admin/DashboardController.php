<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Response\ApiResponse;
use App\Models\AttendanceSession;
use App\Models\Period;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    /**
     * Dashboard counters and open sessions for AJAX UI.
     */
    public function __invoke(): JsonResponse
    {
        $openSessions = AttendanceSession::query()
            ->with(['courseSection', 'period'])
            ->where('status', 'open')
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (AttendanceSession $session) => [
                'id' => $session->id,
                'title' => $session->title,
                'course_section_code' => $session->courseSection?->code,
                'period_name' => $session->period?->name,
                'live_url' => route('sessions.live', $session),
            ]);

        return ApiResponse::success([
            'counts' => [
                'students' => User::query()->where('role', UserRole::Student)->count(),
                'lecturers' => User::query()->where('role', UserRole::Lecturer)->count(),
                'subjects' => Subject::query()->count(),
                'periods' => Period::query()->count(),
            ],
            'open_sessions' => $openSessions,
        ]);
    }
}
