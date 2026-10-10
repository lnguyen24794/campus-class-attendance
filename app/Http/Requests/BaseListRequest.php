<?php

namespace App\Http\Requests;

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
     * Common field rules (including `limit`) come from CommonRules via BaseRequest.
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
}
