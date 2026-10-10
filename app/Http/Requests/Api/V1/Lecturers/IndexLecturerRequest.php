<?php

namespace App\Http\Requests\Api\V1\Lecturers;

use App\Http\Requests\BaseListRequest;

/**
 * List query for admin lecturers index (?q=&page=&limit=).
 */
class IndexLecturerRequest extends BaseListRequest
{
    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return $this->listPresenceRules();
    }
}
