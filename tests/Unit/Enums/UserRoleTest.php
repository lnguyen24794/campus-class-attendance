<?php

namespace Tests\Unit\Enums;

use App\Enums\UserRole;
use PHPUnit\Framework\TestCase;

class UserRoleTest extends TestCase
{
    public function test_cases(): void
    {
        $this->assertSame(
            ['student', 'lecturer', 'admin'],
            array_column(UserRole::cases(), 'value'),
        );
        $this->assertSame(UserRole::Student, UserRole::from('student'));
        $this->assertSame(UserRole::Lecturer, UserRole::from('lecturer'));
        $this->assertSame(UserRole::Admin, UserRole::from('admin'));
    }
}
