<?php

namespace App\Support;

use App\Models\Attendance;

final class AttendanceSuccessMessage
{
    /**
     * MSG-05: Bạn đã điểm danh {môn học} - {tiết học} - {trạng thái}
     */
    public static function from(Attendance $attendance): string
    {
        $attendance->loadMissing([
            'courseSection.subject',
            'attendanceSession.period',
        ]);

        $subject = $attendance->courseSection?->subject?->name
            ?? $attendance->courseSection?->name
            ?? $attendance->courseSection?->code
            ?? 'môn học';

        $period = $attendance->attendanceSession?->period?->name
            ?? ($attendance->attendanceSession?->period
                ? 'Tiết '.$attendance->attendanceSession->period->number
                : 'Không gắn tiết');

        $status = $attendance->status?->label() ?? '—';

        return __('messages.MSG-05', [
            'subject' => $subject,
            'period' => $period,
            'status' => $status,
        ]);
    }
}
