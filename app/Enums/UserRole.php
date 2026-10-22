<?php

namespace App\Enums;

enum UserRole: string
{
    case Manager = 'manager';
    case FieldStaff = 'field_staff';
    case Citizen = 'citizen';

    public function label(): string
    {
        return match ($this) {
            self::Manager => 'Pengelola',
            self::FieldStaff => 'Petugas lapangan',
            self::Citizen => 'Warga',
        };
    }
}
