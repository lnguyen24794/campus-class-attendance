<?php

namespace App\Http\Requests;

abstract class BaseRequest extends BaseFormRequest
{
    public const ACCEPTED = 'accepted';

    public const ARRAY = 'array';

    public const BAIL = 'bail';

    public const BOOLEAN = 'boolean';

    public const DATE = 'date';

    public const DATE_FORMAT = 'date_format';

    public const EMAIL = 'email';

    public const INTEGER = 'integer';

    public const MAX = 'max';

    public const MIN = 'min';

    public const NULLABLE = 'nullable';

    public const NUMERIC = 'numeric';

    public const REQUIRED = 'required';

    public const SOMETIMES = 'sometimes';

    public const STRING = 'string';

    public const UUID = 'uuid';

    /**
     * Module field rules keyed by field name (from module *Rules classes).
     *
     * @var array<string, list<mixed>>
     */
    protected array $commonRules = [];

    /**
     * Determine if the user is authorized to make this request.
     *
     * Role checks are handled by route middleware (`auth`, `role:*`).
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Convert string "null" to actual null before validation.
     *
     * @return array<string, mixed>
     */
    public function validationData(): array
    {
        $validationData = parent::validationData();

        array_walk_recursive($validationData, function (&$value): void {
            if ($value === 'null') {
                $value = null;
            }
        });

        return $validationData;
    }

    /**
     * Type / format / unique / exists rules for this feature module.
     *
     * Override in each FormRequest (or a module base) and return the matching
     * `App\Http\Requests\Common\{Module}Rules::definitions(...)`.
     *
     * @return array<string, list<mixed>>
     */
    protected function moduleCommonRules(): array
    {
        return [];
    }

    /**
     * Merge endpoint presence rules (REQUIRED / NULLABLE) with module *Rules.
     *
     * API Request classes must only pass presence tokens per field; type / format /
     * unique / exists live in the module Rules class.
     *
     * @param  array<string, list<mixed>>  $rules
     * @return array<string, list<mixed>>
     */
    protected function mergeCommonRules(array $rules): array
    {
        $this->commonRules = $this->moduleCommonRules();

        $mergedRules = [];

        foreach ($rules as $name => $value) {
            if (! is_array($value)) {
                continue;
            }

            $this->assertPresenceOnlyRules($name, $value);

            $default = [self::BAIL];

            if ($this->existsCommonRules($name)) {
                $mergedRules[$name] = array_merge($default, $value, $this->findCommonRules($name));
            } else {
                $mergedRules[$name] = array_merge($default, $value);
            }
        }

        return $mergedRules;
    }

    /**
     * Ensure API FormRequests only declare REQUIRED / NULLABLE (optional SOMETIMES).
     *
     * @param  list<mixed>  $value
     */
    private function assertPresenceOnlyRules(string $field, array $value): void
    {
        $allowed = [self::REQUIRED, self::NULLABLE, self::SOMETIMES];

        foreach ($value as $token) {
            if (! is_string($token) || ! in_array($token, $allowed, true)) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Field [%s] in %s must only declare REQUIRED, NULLABLE, or SOMETIMES. Move type/format/unique rules to the module *Rules class.',
                        $field,
                        static::class,
                    ),
                );
            }
        }
    }

    /**
     * Check whether a module rule exists for the field.
     */
    private function existsCommonRules(string $name): bool
    {
        return array_key_exists($name, $this->commonRules);
    }

    /**
     * Find module rules for the field.
     *
     * @return list<mixed>
     */
    private function findCommonRules(string $name): array
    {
        return $this->commonRules[$name] ?? [];
    }
}
