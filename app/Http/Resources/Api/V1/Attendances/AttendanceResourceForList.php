<?php

namespace App\Http\Resources\Api\V1\Attendances;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResourceForList extends JsonResource
{
    /**
     * Transform the attendance model for list/collection responses.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status?->value ?? $this->status,
            'status_label' => $this->status?->label(),
            'checked_in_at' => $this->checked_in_at?->toAtomString(),
            'course_section_id' => $this->course_section_id,
            'course_section_code' => $this->courseSection?->code,
            'course_section_name' => $this->courseSection?->name,
            'session_id' => $this->attendance_session_id,
            'session_title' => $this->attendanceSession?->title,
            'created_at' => $this->created_at?->toAtomString(),
        ];
    }
}
