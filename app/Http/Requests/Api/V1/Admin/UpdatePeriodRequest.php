<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Http\Requests\BaseRequest;

class UpdatePeriodRequest extends BaseRequest
{
    /**
     * Presence rules for updating a period.
     *
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return $this->mergeCommonRules([
            'number' => [self::REQUIRED],
            'name' => [self::REQUIRED],
            'starts_at' => [self::REQUIRED],
            'ends_at' => [self::REQUIRED],
        ]);
    }
}
