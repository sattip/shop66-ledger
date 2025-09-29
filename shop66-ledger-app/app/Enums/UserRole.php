<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case MANAGER = 'manager';
    case EMPLOYEE = 'employee';
    case VIEWER = 'viewer';

    public static function managementRoles(): array
    {
        return [
            self::ADMIN,
            self::MANAGER,
        ];
    }

    public static function financeRoles(): array
    {
        return [
            self::ADMIN,
            self::MANAGER,
            self::EMPLOYEE,
        ];
    }
}
