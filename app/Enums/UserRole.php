<?php

namespace App\Enums;

/**
 * The three kinds of account in ThikThak.
 */
enum UserRole: string
{
    case Customer = 'customer';
    case Provider = 'provider';
    case Admin = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::Customer => 'Customer',
            self::Provider => 'Service provider',
            self::Admin => 'Admin',
        };
    }

    /**
     * The choice shown on the sign-up form.
     */
    public function signupPitch(): string
    {
        return match ($this) {
            self::Customer => 'Hire a professional',
            self::Provider => 'Offer my services',
            self::Admin => 'Run the platform',
        };
    }

    /**
     * The named route each role lands on after logging in.
     */
    public function dashboardRoute(): string
    {
        return match ($this) {
            self::Customer => 'customer.dashboard',
            self::Provider => 'provider.dashboard',
            self::Admin => 'admin.dashboard',
        };
    }

    /**
     * Roles that people can pick when they sign up. Admins are only created by the seeder.
     *
     * @return array<int, self>
     */
    public static function registrable(): array
    {
        return [self::Customer, self::Provider];
    }
}
