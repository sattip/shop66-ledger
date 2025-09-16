<?php

namespace App\Enums;

enum UserRole: string
{
    case SUPER_ADMIN = 'super-admin';
    case ADMIN = 'admin';
    case STORE_MANAGER = 'store-manager';
    case ACCOUNTANT = 'accountant';
    case STAFF = 'staff';
    case READ_ONLY = 'read-only';

    public static function managementRoles(): array
    {
        return [
            self::SUPER_ADMIN,
            self::ADMIN,
            self::STORE_MANAGER,
        ];
    }

    public static function financeRoles(): array
    {
        return [
            self::SUPER_ADMIN,
            self::ADMIN,
            self::STORE_MANAGER,
            self::ACCOUNTANT,
        ];
    }
}
