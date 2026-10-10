<?php

namespace App\Http\Requests\Common;

use App\Http\Requests\BaseRequest;

/**
 * Attendance session / check-in field rules (UC01 / UC02).
 */
final class AttendanceRules
{
    /**
     * @return array<string, list<mixed>>
     */
    public static function definitions(): array
    {
        return [
            'session_qr_token' => [BaseRequest::STRING],
            'course_section_id' => [BaseRequest::UUID, 'exists:course_sections,id'],
            'period_id' => [BaseRequest::UUID, 'exists:periods,id'],
            'title' => [BaseRequest::STRING, BaseRequest::MAX.':255'],
            'duration_minutes' => [BaseRequest::INTEGER, BaseRequest::MIN.':30', BaseRequest::MAX.':240'],
            'late_after_minutes' => [BaseRequest::INTEGER, BaseRequest::MIN.':0', BaseRequest::MAX.':120'],
            'identifier' => [BaseRequest::STRING, BaseRequest::MAX.':255'],
        ];
    }
}
