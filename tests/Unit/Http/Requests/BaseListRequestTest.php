<?php

namespace Tests\Unit\Http\Requests;

use App\Http\Requests\Api\V1\Students\IndexStudentRequest;
use Tests\TestCase;

class BaseListRequestTest extends TestCase
{
    private function makeRequest(array $query = []): IndexStudentRequest
    {
        $request = IndexStudentRequest::create('/api/v1/admin/students', 'GET', $query);
        $request->setContainer($this->app);
        $request->setRedirector($this->app->make('redirect'));
        $request->setUserResolver(fn () => null);

        // Trigger prepareForValidation via validation pipeline
        $request->validateResolved();

        return $request;
    }

    public function test_list_filters_defaults(): void
    {
        $request = $this->makeRequest();

        $this->assertSame([
            'q' => '',
            'page' => 1,
            'limit' => 10,
        ], $request->listFilters());
    }

    public function test_empty_query_values_are_stripped(): void
    {
        $request = IndexStudentRequest::create('/api/v1/admin/students', 'GET', [
            'q' => '',
            'page' => '',
            'limit' => '20',
        ]);
        $request->setContainer($this->app);
        $request->setRedirector($this->app->make('redirect'));

        $request->validateResolved();

        $this->assertFalse($request->has('q'));
        $this->assertFalse($request->has('page'));
        $this->assertSame(20, $request->listFilters()['limit']);
    }

    public function test_per_page_alias_is_used_for_limit(): void
    {
        $request = $this->makeRequest([
            'per_page' => 25,
            'page' => 2,
            'q' => '  hello  ',
        ]);

        $filters = $request->listFilters();

        $this->assertSame('hello', $filters['q']);
        $this->assertSame(2, $filters['page']);
        $this->assertSame(25, $filters['limit']);
    }

    public function test_validation_data_converts_string_null(): void
    {
        $request = IndexStudentRequest::create('/api/v1/admin/students', 'GET', [
            'q' => 'null',
        ]);
        $request->setContainer($this->app);
        $request->setRedirector($this->app->make('redirect'));
        $request->validateResolved();

        $this->assertSame('', $request->listFilters()['q']);
    }
}
