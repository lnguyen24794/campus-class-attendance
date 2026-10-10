<?php

namespace Tests\Unit\Support;

use App\Models\User;
use App\Support\PaginationMeta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaginationMetaTest extends TestCase
{
    use RefreshDatabase;

    public function test_from_paginator_maps_meta_fields(): void
    {
        User::factory()->student()->count(3)->create();

        $paginator = User::query()->paginate(2, ['*'], 'page', 1);
        $meta = PaginationMeta::from($paginator);

        $this->assertSame(1, $meta['current_page']);
        $this->assertSame(2, $meta['last_page']);
        $this->assertSame(2, $meta['per_page']);
        $this->assertSame(3, $meta['total']);
        $this->assertSame(1, $meta['from']);
        $this->assertSame(2, $meta['to']);
    }

    public function test_from_empty_paginator_has_null_from_to(): void
    {
        $paginator = User::query()->paginate(10);
        $meta = PaginationMeta::from($paginator);

        $this->assertSame(0, $meta['total']);
        $this->assertNull($meta['from']);
        $this->assertNull($meta['to']);
    }
}
