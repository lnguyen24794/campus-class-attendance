<?php

namespace Tests\Unit\Enums;

use App\Enums\AttendanceStatus;
use PHPUnit\Framework\TestCase;

class AttendanceStatusTest extends TestCase
{
    public function test_cases_and_labels(): void
    {
        $this->assertSame(['present', 'late'], array_column(AttendanceStatus::cases(), 'value'));
        $this->assertSame('Đúng giờ', AttendanceStatus::Present->label());
        $this->assertSame('Đi muộn', AttendanceStatus::Late->label());
    }
}
