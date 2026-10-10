<?php

namespace App\Repositories;

use App\Models\Attendance;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class AttendanceRepository
{
    /**
     * Search attendance records for a student, newest first.
     *
     * @param  array{q?: string, limit?: int, page?: int}  $params
     */
    public function searchAttendances(int $studentId, array $params = []): LengthAwarePaginator
    {
        $limit = max(1, min((int) ($params['limit'] ?? 10), 100));
        $page = max(1, (int) ($params['page'] ?? 1));
        $q = trim((string) ($params['q'] ?? ''));

        return Attendance::query()
            ->with(['courseSection', 'attendanceSession'])
            ->where('student_id', $studentId)
            ->when($q !== '', function ($query) use ($q): void {
                $like = '%'.$q.'%';
                $query->where(function ($inner) use ($like): void {
                    $inner->whereHas('courseSection', function ($section) use ($like): void {
                        $section->where('code', 'like', $like)
                            ->orWhere('name', 'like', $like);
                    })->orWhereHas('attendanceSession', function ($session) use ($like): void {
                        $session->where('title', 'like', $like);
                    });
                });
            })
            ->orderByDesc('checked_in_at')
            ->paginate($limit, ['*'], 'page', $page);
    }

    /**
     * Find an attendance by student and session.
     *
     * @param  int  $studentId  Student user id
     * @param  string  $sessionId  Attendance session id
     */
    public function findAttendanceByStudentAndSession(int $studentId, string $sessionId): ?Attendance
    {
        return Attendance::query()
            ->where('student_id', $studentId)
            ->where('attendance_session_id', $sessionId)
            ->first();
    }

    /**
     * Insert a new attendance record.
     *
     * @param  array<string, mixed>  $attributes  Column values
     */
    public function insertAttendance(array $attributes): Attendance
    {
        return Attendance::query()->create($attributes);
    }

    /**
     * List attendances for a session, newest first.
     *
     * @return Collection<int, Attendance>
     */
    public function listAttendancesBySession(string $sessionId)
    {
        return Attendance::query()
            ->with('student')
            ->where('attendance_session_id', $sessionId)
            ->orderByDesc('checked_in_at')
            ->get();
    }
}
