<?php

namespace Tests\Unit\Http\Resources;

use App\Enums\UserRole;
use App\Http\Resources\Api\V1\Periods\PeriodResource;
use App\Http\Resources\Api\V1\Periods\PeriodResourceForList;
use App\Http\Resources\Api\V1\Subjects\SubjectResource;
use App\Http\Resources\Api\V1\Subjects\SubjectResourceForList;
use App\Http\Resources\Api\V1\Users\UserResource;
use App\Http\Resources\Api\V1\Users\UserResourceForList;
use App\Models\Period;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class ResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_resources_to_array_keys(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Student,
            'code' => 'SVRES',
        ]);
        $request = Request::create('/');

        $detail = (new UserResource($user))->toArray($request);
        $this->assertSame(
            ['id', 'code', 'name', 'email', 'role', 'created_at'],
            array_keys($detail),
        );

        $list = (new UserResourceForList($user))->toArray($request);
        $this->assertSame(
            ['id', 'code', 'name', 'email', 'role'],
            array_keys($list),
        );
    }

    public function test_subject_resources_to_array_keys(): void
    {
        $subject = Subject::query()->create([
            'code' => 'RES01',
            'name' => 'Resource Subject',
            'credits' => 3,
            'description' => 'Desc',
        ]);
        $request = Request::create('/');

        $detail = (new SubjectResource($subject))->toArray($request);
        $this->assertSame(
            ['id', 'code', 'name', 'credits', 'description', 'created_at'],
            array_keys($detail),
        );

        $list = (new SubjectResourceForList($subject))->toArray($request);
        $this->assertSame(
            ['id', 'code', 'name', 'credits', 'description'],
            array_keys($list),
        );
    }

    public function test_period_resources_to_array_keys(): void
    {
        $period = Period::query()->create([
            'number' => 3,
            'name' => 'Resource Period',
            'starts_at' => '09:00:00',
            'ends_at' => '09:50:00',
        ]);
        $request = Request::create('/');

        $detail = (new PeriodResource($period))->toArray($request);
        $this->assertSame(
            ['id', 'number', 'name', 'starts_at', 'ends_at', 'created_at'],
            array_keys($detail),
        );
        $this->assertSame('09:00', $detail['starts_at']);

        $list = (new PeriodResourceForList($period))->toArray($request);
        $this->assertSame(
            ['id', 'number', 'name', 'starts_at', 'ends_at'],
            array_keys($list),
        );
    }
}
