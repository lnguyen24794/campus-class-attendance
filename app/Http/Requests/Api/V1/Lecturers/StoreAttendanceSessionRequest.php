<?php

namespace App\Http\Requests\Api\V1\Lecturers;

use App\Http\Requests\BaseRequest;

class StoreAttendanceSessionRequest extends BaseRequest
{
    /**
     * Presence rules for opening an attendance session.
     *
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return $this->mergeCommonRules([
            'course_section_id' => [self::REQUIRED],
            'period_id' => [self::NULLABLE],
            'title' => [self::REQUIRED],
            'duration_minutes' => [self::NULLABLE],
            'late_after_minutes' => [self::NULLABLE],
        ]);
    }
}
