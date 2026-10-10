<?php

namespace App\Http\Resources\Api\V1\AttendanceSessions;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceSessionResource extends JsonResource
{
    /**
     * Transform the attendance session model for a single-resource response.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'session_id' => $this->id,
            'qr_token' => $this->qr_token,
            'qr_expires_at' => $this->qr_expires_at?->toAtomString(),
            'closes_at' => $this->closes_at?->toAtomString(),
            'title' => $this->title,
            'status' => $this->status?->value ?? $this->status,
            'status_label' => $this->status?->label(),
            'opens_at' => $this->opens_at?->toAtomString(),
        ];
    }
}
