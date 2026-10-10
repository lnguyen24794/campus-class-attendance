<?php

namespace Tests\Unit\Services;

use App\Services\PublicUrlService;
use Tests\TestCase;

class PublicUrlServiceTest extends TestCase
{
    public function test_base_url_uses_public_url_when_set(): void
    {
        config([
            'app.public_url' => 'https://tunnel.example.com/',
            'app.url' => 'http://localhost',
        ]);

        $service = new PublicUrlService;

        $this->assertSame('https://tunnel.example.com', $service->baseUrl());
        $this->assertSame(
            'https://tunnel.example.com/check-in/abc-token',
            $service->checkInUrl('abc-token'),
        );
    }

    public function test_base_url_falls_back_to_app_url(): void
    {
        config([
            'app.public_url' => '',
            'app.url' => 'http://fallback.test/',
        ]);

        $service = new PublicUrlService;

        $this->assertSame('http://fallback.test', $service->baseUrl());
        $this->assertSame('http://fallback.test/check-in/tok', $service->checkInUrl('tok'));
    }
}
