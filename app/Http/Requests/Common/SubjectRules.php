<?php

namespace App\Http\Requests\Common;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

/**
 * Subject master field rules.
 */
final class SubjectRules
{
    /**
     * @return array<string, list<mixed>>
     */
    public static function definitions(BaseRequest $request): array
    {
        $subjectId = $request->route('subject')?->id;

        return [
            'code' => [
                BaseRequest::STRING,
                BaseRequest::MAX.':32',
                Rule::unique('subjects', 'code')->ignore($subjectId),
            ],
            'name' => [BaseRequest::STRING, BaseRequest::MAX.':255'],
            'credits' => [BaseRequest::INTEGER, BaseRequest::MIN.':1', BaseRequest::MAX.':10'],
            'description' => [BaseRequest::STRING, BaseRequest::MAX.':2000'],
        ];
    }
}
