<?php

namespace App\Http\Requests\Common;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

/**
 * Student / lecturer (users table) field rules.
 */
final class UserRules
{
    /**
     * @return array<string, list<mixed>>
     */
    public static function definitions(BaseRequest $request): array
    {
        $userId = $request->route('student')?->id
            ?? $request->route('lecturer')?->id
            ?? null;

        return [
            'name' => [BaseRequest::STRING, BaseRequest::MAX.':255'],
            'email' => [
                BaseRequest::EMAIL,
                BaseRequest::MAX.':255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'password' => [BaseRequest::STRING, BaseRequest::MIN.':6'],
            'code' => [
                BaseRequest::STRING,
                BaseRequest::MAX.':32',
                Rule::unique('users', 'code')->ignore($userId),
            ],
        ];
    }
}
