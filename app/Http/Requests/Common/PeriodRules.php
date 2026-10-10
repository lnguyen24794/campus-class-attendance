<?php

namespace App\Http\Requests\Common;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

/**
 * Period master field rules.
 */
final class PeriodRules
{
    /**
     * @return array<string, list<mixed>>
     */
    public static function definitions(BaseRequest $request): array
    {
        $periodId = $request->route('period')?->id;

        return [
            'number' => [
                BaseRequest::INTEGER,
                BaseRequest::MIN.':1',
                BaseRequest::MAX.':20',
                Rule::unique('periods', 'number')->ignore($periodId),
            ],
            'name' => [BaseRequest::STRING, BaseRequest::MAX.':255'],
            'starts_at' => [BaseRequest::DATE_FORMAT.':H:i'],
            'ends_at' => [BaseRequest::DATE_FORMAT.':H:i', 'after:starts_at'],
        ];
    }
}
