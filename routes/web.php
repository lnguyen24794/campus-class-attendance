<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;
use Illuminate\Http\Response;
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
| Coverage HTML from: php artisan test --coverage --coverage-html=public/coverage
| https://laravel.com/framework/docs/testing#reporting-test-coverage
|
| Inject <base href="/coverage/"> into HTML so folder/file links work even without a trailing slash.
*/
if (app()->environment(['local', 'testing'])) {
    Route::get('/coverage/{path?}', function (?string $path = null): BinaryFileResponse|Response {
        $root = realpath(public_path('coverage'));
        abort_unless($root !== false && is_dir($root), 404, 'Run `composer coverage` first.');

        $relative = ($path === null || $path === '') ? 'index.html' : $path;
        $candidate = $root.DIRECTORY_SEPARATOR.str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relative);

        if (is_dir($candidate)) {
            $candidate = rtrim($candidate, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'index.html';
        }

        $target = realpath($candidate);
        $rootPrefix = $root.DIRECTORY_SEPARATOR;

        abort_unless(
            $target !== false && str_starts_with($target, $rootPrefix) && is_file($target),
            404,
        );

        // HTML: force base URL so relative links (Enums/index.html, _css/...) resolve under /coverage/
        if (str_ends_with(strtolower($target), '.html')) {
            $html = file_get_contents($target);
            abort_unless($html !== false, 404);

            if (! str_contains($html, '<base ')) {
                $html = preg_replace(
                    '/<head([^>]*)>/i',
                    '<head$1><base href="'.e(url('/coverage/')).'">',
                    $html,
                    1,
                ) ?? $html;
            }

            return response($html, 200)->header('Content-Type', 'text/html; charset=UTF-8');
        }

        return response()->file($target);
    })->where('path', '.*')->name('coverage.report');
}
