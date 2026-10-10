<?php

namespace App\Enums;

enum UserRole: string
{
    case Student = 'student';
    case Lecturer = 'lecturer';
    case Admin = 'admin';
}
