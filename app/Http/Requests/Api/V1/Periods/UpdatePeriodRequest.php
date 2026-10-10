<?php

namespace App\Http\Requests\Api\V1\Periods;

use App\Http\Requests\BaseRequest;
use App\Http\Requests\Common\PeriodRules;

class UpdatePeriodRequest extends BaseRequest
{
    /**
     * @return array<string, list<mixed>>
     */
    protected function moduleCommonRules(): array
    {
        return PeriodRules::definitions($this);
    }

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
