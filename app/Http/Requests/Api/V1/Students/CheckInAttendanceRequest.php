<?php

namespace App\Http\Requests\Api\V1\Students;

use App\Http\Requests\BaseRequest;

class CheckInAttendanceRequest extends BaseRequest
{
    /**
     * Presence rules for QR check-in.
     *
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return $this->mergeCommonRules([
            'session_qr_token' => [self::REQUIRED],
        ]);
    }
}
