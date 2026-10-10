<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Http\Requests\BaseRequest;

class UpdateSubjectRequest extends BaseRequest
{
    /**
     * Presence rules for updating a subject.
     *
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return $this->mergeCommonRules([
            'code' => [self::REQUIRED],
            'name' => [self::REQUIRED],
            'credits' => [self::REQUIRED],
            'description' => [self::NULLABLE],
        ]);
    }
}
