<?php

namespace App\Http\Requests\Api\V1\Students;

use App\Http\Requests\BaseListRequest;

class IndexStudentAttendanceRequest extends BaseListRequest
{
    /**
     * Presence rules for listing student attendances.
     *
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return $this->mergeCommonRules([
            'q' => [self::NULLABLE],
            'page' => [self::NULLABLE],
            'limit' => [self::NULLABLE],
            'per_page' => [self::NULLABLE],
        ]);
    }

    /**
     * @return array{q: string, page: int, limit: int}
     */
    public function listFilters(int $defaultLimit = 10): array
    {
        $validated = $this->validated();

        return [
            'q' => trim((string) ($validated['q'] ?? '')),
            'page' => max(1, (int) ($validated['page'] ?? 1)),
            'limit' => max(1, min(
                (int) ($validated['limit'] ?? $validated['per_page'] ?? $defaultLimit),
                100,
            )),
        ];
    }
}
