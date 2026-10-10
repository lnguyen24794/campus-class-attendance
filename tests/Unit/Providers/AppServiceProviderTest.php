<?php

namespace Tests\Unit\Providers;

use App\Providers\AppServiceProvider;
use Tests\TestCase;

class AppServiceProviderTest extends TestCase
{
    public function test_provider_is_registered_and_boots(): void
    {
        $providers = array_map(
            static fn (object $provider): string => $provider::class,
            $this->app->getProviders(AppServiceProvider::class),
        );

        $this->assertContains(AppServiceProvider::class, $providers);

        $provider = new AppServiceProvider($this->app);
        $provider->register();
        $provider->boot();

        $this->assertInstanceOf(AppServiceProvider::class, $provider);
    }
}
