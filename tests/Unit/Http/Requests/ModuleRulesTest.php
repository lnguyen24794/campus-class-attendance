<?php

namespace Tests\Unit\Http\Requests;

use App\Http\Requests\Api\V1\Students\StoreStudentRequest;
use App\Http\Requests\BaseRequest;
use App\Http\Requests\Common\AttendanceRules;
use App\Http\Requests\Common\ListRules;
use App\Http\Requests\Common\PeriodRules;
use App\Http\Requests\Common\SubjectRules;
use App\Http\Requests\Common\UserRules;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class ModuleRulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_module_rules_definitions_return_expected_keys(): void
    {
        $request = StoreStudentRequest::createFrom(
            StoreStudentRequest::create('/api/v1/admin/students', 'POST'),
            new StoreStudentRequest,
        );

        $this->assertSame(
            ['page', 'limit', 'per_page', 'q', 'status'],
            array_keys(ListRules::definitions()),
        );

        $userKeys = array_keys(UserRules::definitions($request));
        $this->assertSame(['name', 'email', 'password', 'code'], $userKeys);

        $subjectKeys = array_keys(SubjectRules::definitions($request));
        $this->assertSame(['code', 'name', 'credits', 'description'], $subjectKeys);

        $periodKeys = array_keys(PeriodRules::definitions($request));
        $this->assertSame(['number', 'name', 'starts_at', 'ends_at'], $periodKeys);

        $attendanceKeys = array_keys(AttendanceRules::definitions());
        $this->assertContains('session_qr_token', $attendanceKeys);
        $this->assertContains('course_section_id', $attendanceKeys);
        $this->assertContains('identifier', $attendanceKeys);
    }

    public function test_presence_only_rules_throw_when_subclass_returns_non_presence_tokens(): void
    {
        $request = new class extends BaseRequest
        {
            public function rules(): array
            {
                return $this->mergeCommonRules([
                    'name' => [self::REQUIRED, self::STRING],
                ]);
            }
        };

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must only declare REQUIRED, NULLABLE, or SOMETIMES');

        $request->rules();
    }

    public function test_merge_common_rules_skips_non_arrays_and_fields_without_module_rules(): void
    {
        $request = new class extends BaseRequest
        {
            public function rules(): array
            {
                return $this->mergeCommonRules([
                    'custom_flag' => [self::REQUIRED],
                    'ignored' => 'not-an-array',
                ]);
            }
        };

        $rules = $request->rules();

        $this->assertArrayHasKey('custom_flag', $rules);
        $this->assertSame([BaseRequest::BAIL, BaseRequest::REQUIRED], $rules['custom_flag']);
        $this->assertArrayNotHasKey('ignored', $rules);
    }

    public function test_validation_data_converts_nested_string_null(): void
    {
        $request = StoreStudentRequest::create('/api/v1/admin/students', 'POST', [
            'name' => 'null',
            'email' => 'a@cca.test',
            'code' => 'SVNULL',
            'password' => 'password',
            'nested' => ['note' => 'null'],
        ]);
        $request->setContainer($this->app);

        $data = $request->validationData();

        $this->assertNull($data['name']);
        $this->assertNull($data['nested']['note']);
    }
}
