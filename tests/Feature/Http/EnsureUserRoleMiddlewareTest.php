<?php

namespace Tests\Feature\Http;

use App\Enums\UserRole;
use App\Http\Middleware\EnsureUserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class EnsureUserRoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_web_request_redirects_to_login(): void
    {
        $middleware = new EnsureUserRole;
        $request = Request::create('/dashboard', 'GET');

        $response = $middleware->handle($request, fn () => response('ok'), 'admin', 'lecturer');

        $this->assertTrue($response->isRedirect(route('login')));
    }

    public function test_unauthenticated_api_request_returns_401_json(): void
    {
        $middleware = new EnsureUserRole;
        $request = Request::create('/api/v1/admin/students', 'GET');
        $request->headers->set('Accept', 'application/json');

        $response = $middleware->handle($request, fn () => response('ok'), 'admin', 'lecturer');

        $this->assertSame(401, $response->getStatusCode());
        $this->assertSame('Unauthenticated.', $response->getData(true)['message']);
    }

    public function test_forbidden_web_request_aborts_403(): void
    {
        $student = User::factory()->student()->create(['code' => 'SVROLE']);
        $middleware = new EnsureUserRole;
        $request = Request::create('/dashboard', 'GET');
        $request->setUserResolver(fn () => $student);

        try {
            $middleware->handle($request, fn () => response('ok'), 'admin', 'lecturer');
            $this->fail('Expected HttpException');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }
    }

    public function test_forbidden_api_request_returns_403_json(): void
    {
        $student = User::factory()->student()->create(['code' => 'SVROLE2']);
        $middleware = new EnsureUserRole;
        $request = Request::create('/api/v1/admin/students', 'GET');
        $request->headers->set('Accept', 'application/json');
        $request->setUserResolver(fn () => $student);

        $response = $middleware->handle($request, fn () => response('ok'), 'admin', 'lecturer');

        $this->assertSame(403, $response->getStatusCode());
        $this->assertSame('Forbidden.', $response->getData(true)['message']);
    }

    public function test_allowed_role_passes_through(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
            'code' => 'ADMROLE',
        ]);
        $middleware = new EnsureUserRole;
        $request = Request::create('/dashboard', 'GET');
        $request->setUserResolver(fn () => $admin);

        $response = $middleware->handle($request, fn () => response('passed'), 'admin', 'lecturer');

        $this->assertSame('passed', $response->getContent());
    }
}
