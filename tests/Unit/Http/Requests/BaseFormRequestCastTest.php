<?php

namespace Tests\Unit\Http\Requests;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Support\Facades\Validator;
use RuntimeException;
use Tests\TestCase;

class BaseFormRequestCastTest extends TestCase
{
    public function test_cast_integer_float_and_bool(): void
    {
        $request = new class extends BaseFormRequest
        {
            protected array $casts = [
                'count' => 'integer',
                'ratio' => 'float',
                'active' => 'bool',
            ];

            public function rules(): array
            {
                return [
                    'count' => ['required'],
                    'ratio' => ['required'],
                    'active' => ['required'],
                ];
            }

            public function authorize(): bool
            {
                return true;
            }
        };

        $request->replace([
            'count' => '12',
            'ratio' => '1.5',
            'active' => 'true',
        ]);
        $request->setContainer($this->app);
        $request->setRedirector($this->app->make('redirect'));
        $request->setValidator(Validator::make($request->all(), $request->rules()));

        $validated = $request->validated();

        $this->assertSame(12, $validated['count']);
        $this->assertSame(1.5, $validated['ratio']);
        $this->assertSame(1, $validated['active']);
    }

    public function test_shaped_casts_and_invalid_bool_throws(): void
    {
        $request = new class extends BaseFormRequest
        {
            protected array $casts = [
                'active' => 'bool',
                'count' => 'integer',
            ];

            public function rules(): array
            {
                return [
                    'active' => ['nullable'],
                    'count' => ['nullable'],
                ];
            }

            public function authorize(): bool
            {
                return true;
            }
        };

        $request->replace(['active' => '1', 'count' => '5']);
        $request->setContainer($this->app);

        $this->assertSame(1, $request->shaped('active'));
        $this->assertSame(5, $request->shaped('count'));

        $request->replace(['active' => 'maybe']);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Boolean Type Cast Error');
        $request->shaped('active');
    }

    public function test_validated_casts_false_bool(): void
    {
        $request = new class extends BaseFormRequest
        {
            protected array $casts = [
                'active' => 'bool',
            ];

            public function rules(): array
            {
                return [
                    'active' => ['required'],
                ];
            }

            public function authorize(): bool
            {
                return true;
            }
        };

        $request->replace(['active' => 'false']);
        $request->setContainer($this->app);
        $request->setRedirector($this->app->make('redirect'));
        $request->setValidator(Validator::make($request->all(), $request->rules()));

        $this->assertSame(0, $request->validated('active'));
    }

    public function test_shaped_array_nested_casts_and_null_fallback(): void
    {
        $request = new class extends BaseFormRequest
        {
            protected array $casts = [
                'items.*.score' => 'integer',
                'meta.ratio' => 'float',
                'missing' => 'integer',
            ];

            public function rules(): array
            {
                return [
                    'items' => ['nullable', 'array'],
                    'items.*.score' => ['nullable'],
                    'meta' => ['nullable', 'array'],
                    'meta.ratio' => ['nullable'],
                ];
            }

            public function authorize(): bool
            {
                return true;
            }
        };

        $request->replace([
            'items' => [
                ['score' => '9'],
                ['score' => '3'],
            ],
            'meta' => ['ratio' => '2.25'],
            'missing' => null,
        ]);
        $request->setContainer($this->app);

        $shapedItems = $request->shaped('items');
        $this->assertSame(9, $shapedItems[0]['score']);
        $this->assertSame(3, $shapedItems[1]['score']);

        $shapedMeta = $request->shaped('meta');
        $this->assertSame(2.25, $shapedMeta['ratio']);

        $this->assertNull($request->shaped('missing'));
        $this->assertNull($request->shaped('absent'));
    }

    public function test_prepare_for_validation_snake_cases_nested_keys(): void
    {
        $request = new class extends BaseFormRequest
        {
            public function rules(): array
            {
                return [
                    'user_name' => ['nullable'],
                    'profile' => ['nullable', 'array'],
                    'profile.full_name' => ['nullable'],
                ];
            }

            public function authorize(): bool
            {
                return true;
            }
        };

        $request = $request::create('/', 'POST', [
            'userName' => 'Ada',
            'profile' => ['fullName' => 'Ada Lovelace'],
        ]);
        $request->setContainer($this->app);
        $request->setRedirector($this->app->make('redirect'));
        $request->validateResolved();

        $this->assertSame('Ada', $request->input('user_name'));
        $this->assertSame('Ada Lovelace', $request->input('profile.full_name'));
    }

    public function test_validated_casts_nested_list_with_wildcard_cast(): void
    {
        $request = new class extends BaseFormRequest
        {
            protected array $casts = [
                'scores.*' => 'integer',
            ];

            public function rules(): array
            {
                return [
                    'scores' => ['required', 'array'],
                    'scores.*' => ['required'],
                ];
            }

            public function authorize(): bool
            {
                return true;
            }
        };

        $request->replace(['scores' => ['1', '2', '3']]);
        $request->setContainer($this->app);
        $request->setRedirector($this->app->make('redirect'));
        $request->setValidator(Validator::make($request->all(), $request->rules()));

        $this->assertSame([1, 2, 3], $request->validated('scores'));
    }

    public function test_shaped_numeric_parent_key_uses_star_cast(): void
    {
        $request = new class extends BaseFormRequest
        {
            protected array $casts = [
                '*' => 'integer',
            ];

            public function rules(): array
            {
                return [];
            }

            public function authorize(): bool
            {
                return true;
            }
        };

        // parentCastTargetKey === '0' forces the '*' cast-target branch
        $request->replace(['0' => ['10', '20']]);
        $request->setContainer($this->app);

        $this->assertSame([10, 20], $request->shaped('0'));
    }
}
