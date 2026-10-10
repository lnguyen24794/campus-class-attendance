<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

abstract class BaseFormRequest extends FormRequest
{
    /**
     * Casting rules applied after validation (query/body values).
     *
     * @var array<string, string>
     */
    protected array $casts = [];

    /**
     * Normalize request keys to snake_case before validation.
     */
    protected function prepareForValidation(): void
    {
        $validationData = parent::validationData();
        $this->replace($this->convertKeysToSnakeCase($validationData));
    }

    /**
     * Get a cast value for a shaped/nested key.
     */
    public function shaped(string $key): mixed
    {
        $value = $this->get($key, null);

        return $this->castShaped($key, $value);
    }

    /**
     * Return validated data with configured casts applied.
     *
     * @param  string|null  $key  Optional nested key
     * @param  mixed  $default  Default when key is missing
     *
     * @throws ValidationException
     */
    public function validated($key = null, $default = null): mixed
    {
        $validatedData = $this->validator->validated();
        $rules = $this->rules();

        $ruleKeys = array_keys($rules);
        $castKeys = array_keys($this->casts);
        $castTargetKeys = array_intersect($ruleKeys, $castKeys);

        if ($castTargetKeys !== []) {
            $validatedData = array_merge($validatedData, $this->castValidated($validatedData));
        }

        if ($key !== null) {
            return data_get($validatedData, $key, $default);
        }

        return $validatedData;
    }

    /**
     * Cast a shaped value using `$casts`.
     */
    private function castShaped(string $key, mixed $value): mixed
    {
        if (is_array($value)) {
            return $this->castValidated($value, $key);
        }

        if ($value && isset($this->casts[$key])) {
            return $this->cast($this->casts[$key], $value);
        }

        return null;
    }

    /**
     * Recursively convert array keys to snake_case.
     *
     * @param  array<string, mixed>  $array
     * @return array<string, mixed>
     */
    protected function convertKeysToSnakeCase(array $array): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            $snakeKey = is_string($key) ? Str::snake($key) : $key;
            if (is_array($value)) {
                $value = $this->convertKeysToSnakeCase($value);
            }
            $result[$snakeKey] = $value;
        }

        return $result;
    }

    /**
     * Cast validated payload according to `$casts`.
     *
     * @param  array<string|int, mixed>  $validatedData
     * @return array<string|int, mixed>
     */
    private function castValidated(array $validatedData, string $parentCastTargetKey = ''): array
    {
        $castData = [];

        foreach ($validatedData as $key => $value) {
            if (is_numeric($key)) {
                $castTargetKey = ($parentCastTargetKey !== '' && $parentCastTargetKey !== '0')
                    ? ($parentCastTargetKey.'.*')
                    : '*';
            } else {
                $castTargetKey = ($parentCastTargetKey !== '' && $parentCastTargetKey !== '0')
                    ? ($parentCastTargetKey.'.'.$key)
                    : $key;
            }

            if (is_array($value)) {
                $castData[$key] = $this->castValidated($value, $castTargetKey);
            } elseif (isset($this->casts[$castTargetKey])) {
                $castData[$key] = $this->cast($this->casts[$castTargetKey], $value);
            } else {
                $castData[$key] = $value;
            }
        }

        return $castData;
    }

    /**
     * Cast a single value to the configured type.
     *
     * @throws \RuntimeException
     */
    private function cast(string $castType, mixed $value): mixed
    {
        return match ($castType) {
            'integer' => (int) $value,
            'float' => (float) $value,
            'bool' => $this->castBool($value),
            default => $value,
        };
    }

    /**
     * Cast a boolean-like value to 1/0.
     *
     * @throws \RuntimeException
     */
    private function castBool(mixed $value): int
    {
        return match ($value) {
            'true', '1', 1, true => 1,
            'false', '0', 0, false => 0,
            default => throw new \RuntimeException('Boolean Type Cast Error'),
        };
    }
}
