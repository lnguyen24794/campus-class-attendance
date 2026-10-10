<?php

namespace App\Enums;

enum SessionStatus: string
{
    case Open = 'open';
    case Closed = 'closed';

    /**
     * Vietnamese label for UI / messages.
     */
    public function label(): string
    {
        return match ($this) {
            self::Open => 'Đang mở',
            self::Closed => 'Đã đóng',
        };
    }
}
