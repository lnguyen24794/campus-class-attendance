<?php

use App\Http\Controllers\Web\AttendanceSessionLiveController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\PublicCheckInController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
});

Route::get('/check-in/{qrToken}', [PublicCheckInController::class, 'show'])->name('check-in.show');

Route::middleware(['auth', 'role:admin,lecturer'])->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Blade shells — CRUD data loaded via AJAX (/api/v1/admin/*)
    Route::view('/students', 'master.students.index')->name('students.index');
    Route::view('/lecturers', 'master.lecturers.index')->name('lecturers.index');
    Route::view('/subjects', 'master.subjects.index')->name('subjects.index');
    Route::view('/periods', 'master.periods.index')->name('periods.index');

    Route::view('/sessions', 'sessions.index')->name('sessions.index');
    Route::get('/sessions/create', [AttendanceSessionLiveController::class, 'create'])->name('sessions.create');
    Route::get('/sessions/{session}/live', [AttendanceSessionLiveController::class, 'show'])->name('sessions.live');
});
