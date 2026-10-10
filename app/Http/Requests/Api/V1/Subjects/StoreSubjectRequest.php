<?php

namespace App\Http\Requests\Api\V1\Subjects;

use App\Http\Requests\BaseRequest;
use App\Http\Requests\Common\SubjectRules;

class StoreSubjectRequest extends BaseRequest
{
    /**
     * @return array<string, list<mixed>>
     */
    protected function moduleCommonRules(): array
    {
        return SubjectRules::definitions($this);
    }

    /**
     * Presence rules for creating a subject.
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
