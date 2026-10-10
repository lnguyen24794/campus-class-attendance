<?php

use App\Http\Controllers\Api\V1\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Api\V1\Admin\LecturerController as AdminLecturerController;
use App\Http\Controllers\Api\V1\Admin\PeriodController as AdminPeriodController;
use App\Http\Controllers\Api\V1\Admin\StudentController as AdminStudentController;
use App\Http\Controllers\Api\V1\Admin\SubjectController as AdminSubjectController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API v1 — master CRUD + dashboard
|--------------------------------------------------------------------------
| UC01 / UC02 attendance endpoints intentionally removed for demo recreate.
| Spec: docs/use-cases/UC01_*.md , docs/use-cases/UC02_*.md
| Rule: .cursor/rules/cca-qr-attendance.mdc
|
| List query (admin resources):
|   q, page, limit|per_page → meta: current_page, last_page, per_page, total, from, to
*/

Route::prefix('v1')->group(function () {
    // Admin SPA APIs — session cookie + CSRF (web middleware)
    Route::middleware(['web', 'auth', 'role:admin,lecturer'])->prefix('admin')->group(function () {
        Route::get('dashboard', AdminDashboardController::class);

        // Prefix names so they do not collide with Blade web routes.
        Route::apiResource('students', AdminStudentController::class)->names('admin.students');
        Route::apiResource('lecturers', AdminLecturerController::class)->names('admin.lecturers');
        Route::apiResource('subjects', AdminSubjectController::class)->names('admin.subjects');
        Route::apiResource('periods', AdminPeriodController::class)->names('admin.periods');

        // TODO demo: UC01/UC02 — attendance-sessions + student/public check-in
    });
});
