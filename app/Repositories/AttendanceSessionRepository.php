<?php

namespace App\Repositories;

use App\Enums\SessionStatus;
use App\Models\AttendanceSession;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AttendanceSessionRepository
{
    /**
     * Find an attendance session by QR token.
     *
     * @param  string  $qrToken  Short-lived classroom QR token
     */
    public function findAttendanceSessionByQrToken(string $qrToken): ?AttendanceSession
    {
        return AttendanceSession::query()
            ->with('courseSection')
            ->where('qr_token', $qrToken)
            ->first();
    }

    /**
     * Insert a new attendance session.
     *
     * @param  array<string, mixed>  $attributes  Column values
     */
    public function insertAttendanceSession(array $attributes): AttendanceSession
    {
        return AttendanceSession::query()->create($attributes);
    }

    /**
     * Paginate attendance sessions for admin/lecturer history UI.
     *
     * @param  array{q?: string, status?: string, limit?: int, page?: int}  $filters
     */
    public function paginateAttendanceSessions(User $actor, array $filters = []): LengthAwarePaginator
    {
        $limit = max(1, min((int) ($filters['limit'] ?? 10), 100));
        $page = max(1, (int) ($filters['page'] ?? 1));
        $q = trim((string) ($filters['q'] ?? ''));
        $status = $filters['status'] ?? null;

        $likeOperator = AttendanceSession::query()->getConnection()->getDriverName() === 'pgsql'
            ? 'ilike'
            : 'like';

        return AttendanceSession::query()
            ->with(['courseSection.subject', 'period', 'openedBy'])
            ->withCount('attendances')
            ->when($actor->isLecturer(), fn ($query) => $query->where('opened_by', $actor->id))
            ->when($q !== '', function ($query) use ($q, $likeOperator) {
                $like = '%'.$q.'%';
                $query->where(function ($inner) use ($like, $likeOperator) {
                    $inner->where('title', $likeOperator, $like)
                        ->orWhereHas('courseSection', function ($section) use ($like, $likeOperator) {
                            $section->where('code', $likeOperator, $like)
                                ->orWhere('name', $likeOperator, $like);
                        });
                });
            })
            ->when(
                $status !== null && $status !== '' && SessionStatus::tryFrom((string) $status),
                fn ($query) => $query->where('status', $status),
            )
            ->latest('opens_at')
            ->paginate($limit, ['*'], 'page', $page);
    }
}
