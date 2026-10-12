<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Http\Requests\BaseListRequest;

/**
 * List query for attendance session history.
 *
 * Query: ?q=&status=open|closed&page=1&limit=15
 */
class IndexAttendanceSessionRequest extends BaseListRequest
{
    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return $this->mergeCommonRules([
            'q' => [self::NULLABLE],
            'status' => [self::NULLABLE],
            'page' => [self::NULLABLE],
            'limit' => [self::NULLABLE],
            'per_page' => [self::NULLABLE],
        ]);
    }

    /**
     * @return array{q: string, status: string|null, page: int, limit: int}
     */
    public function listFilters(int $defaultLimit = 10): array
    {
        $validated = $this->validated();

        return [
            'q' => trim((string) ($validated['q'] ?? '')),
            'status' => isset($validated['status']) ? (string) $validated['status'] : null,
            'page' => max(1, (int) ($validated['page'] ?? 1)),
            'limit' => max(1, min(
                (int) ($validated['limit'] ?? $validated['per_page'] ?? $defaultLimit),
                100,
            )),
        ];
    }
}
