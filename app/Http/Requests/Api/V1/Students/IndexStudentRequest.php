<?php

namespace App\Http\Requests\Api\V1\Students;

use App\Http\Requests\BaseListRequest;

/**
 * List query for admin students index (?q=&page=&limit=).
 */
class IndexStudentRequest extends BaseListRequest
{
    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return $this->listPresenceRules();
    }
}
