<?php

namespace App\Enums;

enum AttendanceStatus: string
{
    case Present = 'present';
    case Late = 'late';

    /**
     * Vietnamese label for UI / messages.
     */
    public function label(): string
    {
        return match ($this) {
            self::Present => 'Đúng giờ',
            self::Late => 'Đi muộn',
        };
    }
}
