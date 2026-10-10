<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
});

Route::middleware(['auth', 'role:admin,lecturer'])->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Blade shells — CRUD data loaded via AJAX (/api/v1/admin/*)
    Route::view('/students', 'master.students.index')->name('students.index');
    Route::view('/lecturers', 'master.lecturers.index')->name('lecturers.index');
    Route::view('/subjects', 'master.subjects.index')->name('subjects.index');
    Route::view('/periods', 'master.periods.index')->name('periods.index');

    // TODO demo: UC01/UC02 — /sessions, /sessions/create, /sessions/{id}/live, /check-in/{token}
});

/*
| HTML coverage report (PCOV) — local/testing only.
| Generate first: composer coverage
| Open: http://localhost:8000/coverage
| Guide: https://laravel-news.com/generate-code-coverage-in-laravel-with-pcov
*/
if (app()->environment(['local', 'testing'])) {
    Route::get('/coverage/{path?}', function (?string $path = null): BinaryFileResponse {
        $root = realpath(base_path('docs/coverage/html'));
        abort_unless($root !== false && is_dir($root), 404, 'Run `composer coverage` first.');

        $relative = $path === null || $path === '' ? 'index.html' : $path;
        $target = realpath($root.DIRECTORY_SEPARATOR.str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relative));

        abort_unless(
            $target !== false && str_starts_with($target, $root) && is_file($target),
            404,
        );

        return response()->file($target);
    })->where('path', '.*')->name('coverage.report');
}
