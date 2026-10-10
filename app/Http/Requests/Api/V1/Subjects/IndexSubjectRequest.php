<?php

namespace App\Http\Requests\Api\V1\Subjects;

use App\Http\Requests\BaseListRequest;

/**
 * List query for admin subjects index (?q=&page=&limit=).
 */
class IndexSubjectRequest extends BaseListRequest
{
    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return $this->listPresenceRules();
    }
}
