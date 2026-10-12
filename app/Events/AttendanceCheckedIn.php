<?php

namespace App\Events;

use App\Models\Attendance;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AttendanceCheckedIn implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Attendance $attendance,
    ) {
        $this->attendance->loadMissing(['student', 'attendanceSession']);
    }

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('attendance-sessions.'.$this->attendance->attendance_session_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'attendance.checked-in';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->attendance->id,
            'student_name' => $this->attendance->student?->name,
            'student_code' => $this->attendance->student?->code,
            'status' => $this->attendance->status?->value,
            'status_label' => $this->attendance->status?->label(),
            'checked_in_at' => $this->attendance->checked_in_at?->toAtomString(),
        ];
    }
}
