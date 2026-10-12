<?php

use App\Http\Controllers\Api\V1\Admin\AttendanceSessionAdminController;
use App\Http\Controllers\Api\V1\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Api\V1\Admin\LecturerController as AdminLecturerController;
use App\Http\Controllers\Api\V1\Admin\PeriodController as AdminPeriodController;
use App\Http\Controllers\Api\V1\Admin\StudentController as AdminStudentController;
use App\Http\Controllers\Api\V1\Admin\SubjectController as AdminSubjectController;
use App\Http\Controllers\Api\V1\Lecturers\AttendanceSessionController;
use App\Http\Controllers\Api\V1\Public\PublicCheckInController;
use App\Http\Controllers\Api\V1\Students\StudentAttendanceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API v1 — list endpoints support search + pagination
|--------------------------------------------------------------------------
| Query (GET index):
|   q         string  optional — keyword search
|   page      int     optional — page number (default 1)
|   limit     int     optional — page size 1–100 (default 10; options 10/20/50; alias: per_page)
|   status    string  optional — attendance-sessions only (open|closed)
|
| Response meta:
|   current_page, last_page, per_page, total, from, to
|
| UC01 / UC02: docs/use-cases/ · .cursor/rules/cca-qr-attendance.mdc
*/

Route::prefix('v1')->group(function () {
    Route::middleware(['auth', 'role:student'])->prefix('students')->group(function () {
        // GET ?q=&page=&limit=
        Route::get('attendances', [StudentAttendanceController::class, 'index']);
        Route::post('attendances/check-in', [StudentAttendanceController::class, 'checkIn']);
    });

    Route::middleware(['auth', 'role:lecturer'])->prefix('lecturers')->group(function () {
        Route::post('attendance-sessions', [AttendanceSessionController::class, 'store']);
    });

    // Public check-in (QR scan) — no auth
    Route::prefix('public')->group(function () {
        Route::get('check-in/{qrToken}', [PublicCheckInController::class, 'show']);
        Route::post('check-in/{qrToken}', [PublicCheckInController::class, 'store']);
    });

    // Admin SPA APIs — session cookie + CSRF (web middleware)
    Route::middleware(['web', 'auth', 'role:admin,lecturer'])->prefix('admin')->group(function () {
        Route::get('dashboard', AdminDashboardController::class);

        // Prefix names so they do not collide with Blade web routes.
        Route::apiResource('students', AdminStudentController::class)->names('admin.students');
        Route::apiResource('lecturers', AdminLecturerController::class)->names('admin.lecturers');
        Route::apiResource('subjects', AdminSubjectController::class)->names('admin.subjects');
        Route::apiResource('periods', AdminPeriodController::class)->names('admin.periods');

        Route::get('attendance-sessions/options', [AttendanceSessionAdminController::class, 'options']);
        // Index: GET ?q=&status=&page=&limit=
        Route::get('attendance-sessions', [AttendanceSessionAdminController::class, 'index']);
        Route::post('attendance-sessions', [AttendanceSessionAdminController::class, 'store']);
        Route::get('attendance-sessions/{session}/live', [AttendanceSessionAdminController::class, 'live']);
        Route::post('attendance-sessions/{session}/refresh-qr', [AttendanceSessionAdminController::class, 'refreshQr']);
    });
});
