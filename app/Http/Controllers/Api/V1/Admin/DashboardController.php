<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Response\ApiResponse;
use App\Models\Period;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    /**
     * Dashboard counters for AJAX UI.
     *
     * open_sessions left empty until UC02 (Live) is recreated.
     */
    public function __invoke(): JsonResponse
    {
        return ApiResponse::success([
            'counts' => [
                'students' => User::query()->where('role', UserRole::Student)->count(),
                'lecturers' => User::query()->where('role', UserRole::Lecturer)->count(),
                'subjects' => Subject::query()->count(),
                'periods' => Period::query()->count(),
            ],
            'open_sessions' => [],
        ]);
    }
}
