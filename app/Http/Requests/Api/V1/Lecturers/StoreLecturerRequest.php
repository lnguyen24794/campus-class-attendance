<?php

namespace App\Http\Requests\Api\V1\Lecturers;

use App\Http\Requests\BaseRequest;
use App\Http\Requests\Common\UserRules;

class StoreLecturerRequest extends BaseRequest
{
    /**
     * @return array<string, list<mixed>>
     */
    protected function moduleCommonRules(): array
    {
        return UserRules::definitions($this);
    }

    /**
     * Presence rules for creating a lecturer.
     *
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return $this->mergeCommonRules([
            'name' => [self::REQUIRED],
            'email' => [self::REQUIRED],
            'code' => [self::REQUIRED],
            'password' => [self::REQUIRED],
        ]);
    }
}
