<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Http\Requests\BaseRequest;

class UpdateMasterUserRequest extends BaseRequest
{
    /**
     * Presence rules for updating a student or lecturer.
     *
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return $this->mergeCommonRules([
            'name' => [self::REQUIRED],
            'email' => [self::REQUIRED],
            'code' => [self::REQUIRED],
            'password' => [self::NULLABLE],
        ]);
    }
}
