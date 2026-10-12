<?php

namespace Tests\Unit\Exceptions;

use App\Exceptions\AttendanceException;
use Illuminate\Http\Request;
use Tests\TestCase;

class AttendanceExceptionTest extends TestCase
{
    public function test_render_returns_null_for_non_api_html_request(): void
    {
        $exception = AttendanceException::qrExpired();
        $request = Request::create('/sessions', 'GET');
        $request->headers->set('Accept', 'text/html');

        $this->assertNull($exception->render($request));
    }

    public function test_render_returns_json_for_api_request(): void
    {
        $exception = AttendanceException::notEnrolled();
        $request = Request::create('/api/v1/students/attendances/check-in', 'POST');

        $response = $exception->render($request);

        $this->assertNotNull($response);
        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame('MSG-02', $response->getData(true)['code']);
    }
}
