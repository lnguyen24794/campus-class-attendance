<?php

namespace App\Http\Requests\Common;

use App\Http\Requests\BaseRequest;

/**
 * Shared list/query field rules (pagination + search).
 */
final class ListRules
{
    /**
     * @return array<string, list<mixed>>
     */
    public static function definitions(): array
    {
        return [
            'page' => [BaseRequest::INTEGER, BaseRequest::MIN.':1'],
            'limit' => [BaseRequest::INTEGER, BaseRequest::MIN.':1', BaseRequest::MAX.':100'],
            'per_page' => [BaseRequest::INTEGER, BaseRequest::MIN.':1', BaseRequest::MAX.':100'],
            'q' => [BaseRequest::STRING, BaseRequest::MAX.':255'],
            'status' => [BaseRequest::STRING, BaseRequest::MAX.':32'],
        ];
    }
}
