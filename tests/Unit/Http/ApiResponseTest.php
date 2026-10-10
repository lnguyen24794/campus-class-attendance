<?php

namespace Tests\Unit\Http;

use App\Http\Response\ApiResponse;
use Tests\TestCase;

class ApiResponseTest extends TestCase
{
    public function test_success_error_and_helpers(): void
    {
        $success = ApiResponse::success(['ok' => true], 'Done', 200);
        $this->assertSame(200, $success->getStatusCode());
        $this->assertTrue($success->getData(true)['success']);
        $this->assertSame('Done', $success->getData(true)['message']);

        $error = ApiResponse::error('Bad', 400, ['field' => ['invalid']]);
        $this->assertSame(400, $error->getStatusCode());
        $this->assertFalse($error->getData(true)['success']);
        $this->assertSame(['field' => ['invalid']], $error->getData(true)['errors']);

        $created = ApiResponse::created(['id' => 1]);
        $this->assertSame(201, $created->getStatusCode());
        $this->assertSame('Created successfully', $created->getData(true)['message']);

        $notFound = ApiResponse::notFound();
        $this->assertSame(404, $notFound->getStatusCode());

        $forbidden = ApiResponse::forbidden();
        $this->assertSame(403, $forbidden->getStatusCode());

        $unauthorized = ApiResponse::unauthorized();
        $this->assertSame(401, $unauthorized->getStatusCode());

        $validation = ApiResponse::validationError(['email' => ['required']]);
        $this->assertSame(422, $validation->getStatusCode());
        $this->assertSame('Validation failed', $validation->getData(true)['message']);
    }
}
