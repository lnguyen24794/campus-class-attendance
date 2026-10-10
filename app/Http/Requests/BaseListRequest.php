<?php

namespace App\Http\Requests;

use App\Http\Requests\Common\ListRules;

abstract class BaseListRequest extends BaseRequest
{
    /**
     * Casting rules for list/query parameters (always strings from the query string).
     *
     * @var array<string, string>
     */
    protected array $casts = [
        'page' => 'integer',
        'limit' => 'integer',
        'per_page' => 'integer',
    ];

    /**
     * Drop empty query values, then normalize keys to snake_case.
     *
     * List field rules come from ListRules via moduleCommonRules().
     */
    protected function prepareForValidation(): void
    {
        $validationData = parent::validationData();

        foreach ($validationData as $key => $value) {
            if ((empty($value) || $value === 'null') && $value !== 0 && $value !== '0') {
                unset($validationData[$key]);
            }
        }

        $this->replace($this->convertKeysToSnakeCase($validationData));
    }

    /**
     * @return array<string, list<mixed>>
     */
    protected function moduleCommonRules(): array
    {
        return ListRules::definitions();
    }

    /**
     * Presence rules shared by admin index endpoints (?q=&page=&limit=).
     *
     * @return array<string, list<mixed>>
     */
    protected function listPresenceRules(): array
    {
        return $this->mergeCommonRules([
            'q' => [self::NULLABLE],
            'page' => [self::NULLABLE],
            'limit' => [self::NULLABLE],
            'per_page' => [self::NULLABLE],
        ]);
    }

    /**
     * Normalized list filters for repositories/services.
     *
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
