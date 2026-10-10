<?php

namespace App\Http\Requests\Common;

use App\Http\Requests\Api\V1\Admin\StoreSubjectRequest;
use App\Http\Requests\Api\V1\Admin\UpdateSubjectRequest;
use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

/**
 * Shared CCA field validation rules (type / format / unique / exists).
 *
 * API FormRequests only declare REQUIRED or NULLABLE; mergeCommonRules() appends these.
 */
final class CommonRules
{
    /**
     * Build common rules for the current request context (route model → unique ignore).
     *
     * @return array<string, list<mixed>>
     */
    public static function definitions(BaseRequest $request): array
    {
        $userId = $request->route('student')?->id
            ?? $request->route('lecturer')?->id
            ?? null;

        $subjectId = $request->route('subject')?->id;
        $periodId = $request->route('period')?->id;

        return [
            // List / query (pagination + search)
            'page' => [BaseRequest::INTEGER, BaseRequest::MIN.':1'],
            'limit' => [BaseRequest::INTEGER, BaseRequest::MIN.':1', BaseRequest::MAX.':100'],
            'per_page' => [BaseRequest::INTEGER, BaseRequest::MIN.':1', BaseRequest::MAX.':100'],
            'q' => [BaseRequest::STRING, BaseRequest::MAX.':255'],
            'status' => [BaseRequest::STRING, BaseRequest::MAX.':32'],

            // User (student / lecturer)
            'name' => [BaseRequest::STRING, BaseRequest::MAX.':255'],
            'email' => [
                BaseRequest::EMAIL,
                BaseRequest::MAX.':255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'password' => [BaseRequest::STRING, BaseRequest::MIN.':6'],
            'code' => self::codeRules($request, $userId, $subjectId),

            // Subject
            'credits' => [BaseRequest::INTEGER, BaseRequest::MIN.':1', BaseRequest::MAX.':10'],
            'description' => [BaseRequest::STRING, BaseRequest::MAX.':2000'],

            // Period
            'number' => [
                BaseRequest::INTEGER,
                BaseRequest::MIN.':1',
                BaseRequest::MAX.':20',
                Rule::unique('periods', 'number')->ignore($periodId),
            ],
            'starts_at' => [BaseRequest::DATE_FORMAT.':H:i'],
            'ends_at' => [BaseRequest::DATE_FORMAT.':H:i', 'after:starts_at'],

            // Attendance session / check-in
            'session_qr_token' => [BaseRequest::STRING],
            'course_section_id' => [BaseRequest::UUID, 'exists:course_sections,id'],
            'period_id' => [BaseRequest::UUID, 'exists:periods,id'],
            'title' => [BaseRequest::STRING, BaseRequest::MAX.':255'],
            'duration_minutes' => [BaseRequest::INTEGER, BaseRequest::MIN.':30', BaseRequest::MAX.':240'],
            'late_after_minutes' => [BaseRequest::INTEGER, BaseRequest::MIN.':0', BaseRequest::MAX.':120'],

            // Public check-in
            'identifier' => [BaseRequest::STRING, BaseRequest::MAX.':255'],
        ];
    }

    /**
     * `code` maps to users.code or subjects.code depending on the FormRequest / route.
     *
     * @return list<mixed>
     */
    private static function codeRules(BaseRequest $request, mixed $userId, mixed $subjectId): array
    {
        if (self::isSubjectContext($request, $subjectId)) {
            return [
                BaseRequest::STRING,
                BaseRequest::MAX.':32',
                Rule::unique('subjects', 'code')->ignore($subjectId),
            ];
        }

        return [
            BaseRequest::STRING,
            BaseRequest::MAX.':32',
            Rule::unique('users', 'code')->ignore($userId),
        ];
    }

    private static function isSubjectContext(BaseRequest $request, mixed $subjectId): bool
    {
        if ($subjectId !== null) {
            return true;
        }

        return $request instanceof StoreSubjectRequest
            || $request instanceof UpdateSubjectRequest;
    }
}
