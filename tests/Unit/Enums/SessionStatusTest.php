<?php

namespace Tests\Unit\Enums;

use App\Enums\SessionStatus;
use PHPUnit\Framework\TestCase;

class SessionStatusTest extends TestCase
{
    public function test_cases_and_labels(): void
    {
        $this->assertSame(['open', 'closed'], array_column(SessionStatus::cases(), 'value'));
        $this->assertSame('Đang mở', SessionStatus::Open->label());
        $this->assertSame('Đã đóng', SessionStatus::Closed->label());
    }
}
